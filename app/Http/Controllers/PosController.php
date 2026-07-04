<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Http\Requests\PosStkPushRequest;
use App\Services\BranchService;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService,
        protected BranchService $branchService,
    ) {}

    public function index(): View
    {
        return view('pos.index', $this->posService->checkoutData());
    }

    public function store(PosCheckoutRequest $request): RedirectResponse
    {
        $branchId = $this->branchService->currentBranchId();

        if ($branchId === null) {
            return redirect()->route('pos.index')
                ->with('error', 'Select a branch before completing checkout.');
        }

        $receipt = $this->posService->checkout(
            $branchId,
            $request->user()->id,
            $request->validated(),
        );

        return redirect()->route('receipts.show', $receipt)
            ->with('success', 'Sale completed and receipt issued.');
    }

    public function stkPush(PosStkPushRequest $request): JsonResponse
    {
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
