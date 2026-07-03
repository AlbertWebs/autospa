<?php

namespace App\Http\Controllers;

use App\Services\BranchService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected BranchService $branchService,
    ) {}

    public function daily(Request $request): View
    {
        return view('reports.daily', [
            'report' => $this->reportService->daily(
                $this->branchService->currentBranchId(),
                $request->date('date'),
            ),
        ]);
    }

    public function weekly(Request $request): View
    {
        return view('reports.weekly', [
            'report' => $this->reportService->weekly(
                $this->branchService->currentBranchId(),
                $request->date('date'),
            ),
        ]);
    }

    public function monthly(Request $request): View
    {
        return view('reports.monthly', [
            'report' => $this->reportService->monthly(
                $this->branchService->currentBranchId(),
                $request->date('date'),
            ),
        ]);
    }

    public function revenue(): View
    {
        return view('reports.revenue', [
            'report' => $this->reportService->revenue($this->branchService->currentBranchId()),
        ]);
    }

    public function customers(): View
    {
        return view('reports.customers', [
            'report' => $this->reportService->customers($this->branchService->currentBranchId()),
        ]);
    }

    public function staff(): View
    {
        return view('reports.staff', [
            'report' => $this->reportService->staff($this->branchService->currentBranchId()),
        ]);
    }

    public function inventory(): View
    {
        return view('reports.inventory', [
            'report' => $this->reportService->inventory($this->branchService->currentBranchId()),
        ]);
    }
}
