<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected BranchService $branchService,
    ) {}

    public function index(Request $request): View
    {
        $branchId = $this->branchService->currentBranchId();
        $selectedDate = $this->resolveSelectedDate($request->input('date'));

        return view('dashboard.index', [
            'selectedDate' => $selectedDate,
            'stats' => $this->dashboardService->stats($branchId, $selectedDate),
            'chart' => $this->dashboardService->monthlyRevenue($branchId),
            'topEmployees' => $this->dashboardService->topEmployees($branchId),
            'recentActivity' => $this->dashboardService->recentActivity($branchId),
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
