<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
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

    public function revenue(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('mobile.reports.show', [
            'title' => 'Revenue Report',
            'data' => $this->reportService->revenue(null, $from, $to),
            'filterRoute' => route('mobile.reports.revenue'),
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ]);
    }

    public function profit(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('mobile.reports.profit', [
            'title' => 'Profit & Loss',
            'report' => $this->reportService->profit(null, $from, $to),
            'filterRoute' => route('mobile.reports.profit'),
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ]);
    }

    public function customers(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('mobile.reports.customers', [
            'title' => 'Customer Report',
            'report' => $this->reportService->customers(null, $from, $to),
            'filterRoute' => route('mobile.reports.customers'),
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ]);
    }

    public function staff(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('mobile.reports.staff', [
            'title' => 'Staff Report',
            'report' => $this->reportService->staff(null, $from, $to),
            'filterRoute' => route('mobile.reports.staff'),
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
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
