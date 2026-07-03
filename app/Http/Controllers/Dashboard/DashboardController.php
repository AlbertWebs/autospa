<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected BranchService $branchService,
    ) {}

    public function index(): View
    {
        $branchId = $this->branchService->currentBranchId();

        return view('dashboard.index', [
            'stats' => $this->dashboardService->stats($branchId),
            'chart' => $this->dashboardService->monthlyRevenue($branchId),
            'topEmployees' => $this->dashboardService->topEmployees($branchId),
            'recentActivity' => $this->dashboardService->recentActivity($branchId),
        ]);
    }
}
