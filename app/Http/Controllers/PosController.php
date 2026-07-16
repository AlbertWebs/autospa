<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Http\Requests\PosStkPushRequest;
use App\Models\JobCard;
use App\Services\BranchService;
use App\Services\PosService;
use App\Support\PosSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService,
        protected BranchService $branchService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(PosSettings::enabled(), 403);

        $branchId = $this->branchService->currentBranchId();
        $jobCard = null;

        if ($request->filled('job_card') && $branchId !== null) {
            $jobCard = JobCard::query()
                ->where('branch_id', $branchId)
                ->with(['services.service', 'products.product'])
                ->find($request->integer('job_card'));
        }

        return view('pos.index', $this->posService->checkoutData($branchId, $jobCard));
    }

    public function offlineReceipt(): View
    {
        abort_unless(PosSettings::enabled(), 403);

        return view('pos.offline-receipt');
    }

    public function store(PosCheckoutRequest $request): RedirectResponse|JsonResponse
    {
        abort_unless(PosSettings::enabled(), 403);

        $branchId = $this->branchService->currentBranchId();

        if ($branchId === null) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Select a branch before completing checkout.'], 422);
            }

            return redirect()->route('pos.index')
                ->with('error', 'Select a branch before completing checkout.');
        }

        $receipt = $this->posService->checkout(
            $branchId,
            $request->user()->id,
            $request->validated(),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Sale completed and receipt issued.',
                'receipt' => [
                    'id' => $receipt->id,
                    'receipt_number' => $receipt->receipt_number,
                    'amount' => $receipt->amount,
                ],
                'redirect' => route('receipts.show', $receipt),
            ]);
        }

        return redirect()->route('receipts.show', $receipt)
            ->with('success', 'Sale completed and receipt issued.');
    }

    public function stkPush(PosStkPushRequest $request): JsonResponse
    {
        abort_unless(PosSettings::enabled(), 403);

        $result = $this->posService->initiateStkPush(
            $this->branchService->currentBranchId(),
            $request->validated(),
        );

        return response()->json([
            'message' => $result->message ?? 'STK push initiated.',
            'status' => $result->status,
            'transaction_id' => $result->transactionId,
        ]);
    }
}
