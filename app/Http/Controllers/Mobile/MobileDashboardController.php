<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Services\DashboardService;
use Illuminate\View\View;

class MobileDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected BranchService $branchService,
    ) {}

    public function index(): View
    {
        $branchId = $this->branchService->currentBranchId();

        return view('mobile.index', [
            'stats' => $this->dashboardService->stats($branchId),
            'chart' => $this->dashboardService->monthlyRevenue($branchId),
            'snapshot' => $this->dashboardService->operationsSnapshot($branchId),
            'topEmployees' => $this->dashboardService->topEmployees($branchId, 5),
            'recentActivity' => $this->dashboardService->recentActivity($branchId, 8),
        ]);
    }
}
