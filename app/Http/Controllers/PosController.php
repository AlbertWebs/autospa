<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Services\BranchService;
use App\Services\PosService;
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
        $invoice = $this->posService->checkout(
            $this->branchService->currentBranchId(),
            $request->user()->id,
            $request->validated(),
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Checkout completed.');
    }
}
