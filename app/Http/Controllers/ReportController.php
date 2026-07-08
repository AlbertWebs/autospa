<?php

namespace App\Http\Controllers;

use App\Services\BranchService;
use App\Services\ReportService;
use Carbon\Carbon;
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

    public function revenue(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('reports.revenue', [
            'report' => $this->reportService->revenue(
                $this->branchService->currentBranchId(),
                $from,
                $to,
            ),
        ]);
    }

    public function customers(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('reports.customers', [
            'report' => $this->reportService->customers(
                $this->branchService->currentBranchId(),
                $from,
                $to,
            ),
        ]);
    }

    public function staff(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('reports.staff', [
            'report' => $this->reportService->staff(
                $this->branchService->currentBranchId(),
                $from,
                $to,
            ),
        ]);
    }

    public function inventory(Request $request): View
    {
        $asOf = $request->filled('as_of')
            ? Carbon::parse($request->input('as_of'))
            : now();

        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        return view('reports.inventory', [
            'report' => $this->reportService->inventory(
                $this->branchService->currentBranchId(),
                $asOf,
                $from,
                $to,
            ),
        ]);
    }

    public function jobCards(Request $request): View
    {
        return view('reports.job-cards', [
            'report' => $this->reportService->jobCards(
                $this->branchService->currentBranchId(),
                $request->date('date'),
            ),
        ]);
    }

    public function profit(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('reports.profit', [
            'report' => $this->reportService->profit(
                $this->branchService->currentBranchId(),
                $from,
                $to,
            ),
        ]);
    }
}
