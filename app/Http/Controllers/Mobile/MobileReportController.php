<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\View\View;

class MobileReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function index(): View
    {
        return view('mobile.reports.index');
    }

    public function daily(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Daily Report',
            'data' => $this->reportService->daily(),
        ]);
    }

    public function weekly(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Weekly Report',
            'data' => $this->reportService->weekly(),
        ]);
    }

    public function monthly(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Monthly Report',
            'data' => $this->reportService->monthly(),
        ]);
    }

    public function revenue(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Revenue Report',
            'data' => $this->reportService->revenue(),
        ]);
    }

    public function customers(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Customer Report',
            'data' => $this->reportService->customers(),
        ]);
    }

    public function staff(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Staff Report',
            'data' => $this->reportService->staff(),
        ]);
    }

    public function jobCards(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Job Cards Report',
            'data' => $this->reportService->jobCards(),
        ]);
    }

    public function inventory(): View
    {
        return view('mobile.reports.show', [
            'title' => 'Inventory Report',
            'data' => $this->reportService->inventory(),
        ]);
    }
}
