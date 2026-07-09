<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected BranchService $branchService,
    ) {}

    public function index(Request $request): View
    {
        $branchId = $this->branchService->currentBranchId();
        $selectedDate = $this->resolveSelectedDate($request->input('date'));

        return view('mobile.index', [
            'selectedDate' => $selectedDate,
            'stats' => $this->dashboardService->stats($branchId, $selectedDate),
            'chart' => $this->dashboardService->monthlyRevenue($branchId),
            'snapshot' => $this->dashboardService->operationsSnapshot($branchId, $selectedDate),
            'topEmployees' => $this->dashboardService->topEmployees($branchId, 5),
            'recentActivity' => $this->dashboardService->recentActivity($branchId, 8),
        ]);
    }

    protected function resolveSelectedDate(?string $date): Carbon
    {
        if (! $date) {
            return now()->startOfDay();
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return now()->startOfDay();
        }
    }
}
