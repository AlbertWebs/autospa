<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Product;
use App\Models\User;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\JobCardStatus;
use App\Enums\VehicleStatus;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        protected BranchService $branchService,
    ) {}

    public function stats(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        if (! $branchId) {
            return $this->emptyStats();
        }

        $today = now()->toDateString();

        return [
            'today_revenue' => Invoice::query()
                ->where('branch_id', $branchId)
                ->whereDate('issued_at', $today)
                ->whereIn('status', [InvoiceStatus::Paid, InvoiceStatus::PartiallyPaid])
                ->sum('paid_amount'),
            'today_bookings' => Booking::query()
                ->where('branch_id', $branchId)
                ->whereDate('scheduled_at', $today)
                ->count(),
            'vehicles_in_service' => JobCard::query()
                ->where('branch_id', $branchId)
                ->where('status', JobCardStatus::InProgress)
                ->count(),
            'vehicles_ready' => JobCard::query()
                ->where('branch_id', $branchId)
                ->where('status', JobCardStatus::Completed)
                ->whereDate('completed_at', $today)
                ->count(),
            'pending_payments' => Invoice::query()
                ->where('branch_id', $branchId)
                ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid])
                ->sum('balance_due'),
            'low_stock_count' => Product::query()
                ->where('branch_id', $branchId)
                ->whereColumn('quantity_on_hand', '<=', 'minimum_level')
                ->count(),
        ];
    }

    public function monthlyRevenue(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        if (! $branchId) {
            return ['labels' => [], 'data' => []];
        }

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');
            $data[] = (float) Invoice::query()
                ->where('branch_id', $branchId)
                ->whereYear('issued_at', $month->year)
                ->whereMonth('issued_at', $month->month)
                ->sum('paid_amount');
        }

        return compact('labels', 'data');
    }

    public function topEmployees(?int $branchId = null, int $limit = 5): Collection
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        if (! $branchId) {
            return collect();
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->withCount(['assignedJobCards as completed_jobs' => function ($q) {
                $q->where('status', JobCardStatus::Completed)
                    ->whereMonth('completed_at', now()->month);
            }])
            ->orderByDesc('completed_jobs')
            ->limit($limit)
            ->get();
    }

    public function recentActivity(?int $branchId = null, int $limit = 10): Collection
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        if (! $branchId) {
            return collect();
        }

        return ActivityLog::query()
            ->with('user')
            ->where('branch_id', $branchId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    protected function emptyStats(): array
    {
        return [
            'today_revenue' => 0,
            'today_bookings' => 0,
            'vehicles_in_service' => 0,
            'vehicles_ready' => 0,
            'pending_payments' => 0,
            'low_stock_count' => 0,
        ];
    }
}
