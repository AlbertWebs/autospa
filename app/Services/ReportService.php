<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Product;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\JobCardStatus;
use Carbon\Carbon;

class ReportService
{
    public function __construct(
        protected BranchService $branchService,
    ) {}

    public function daily(?int $branchId = null, ?Carbon $date = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $date = $date ?? now();

        return [
            'date' => $date->toDateString(),
            'revenue' => $this->revenueForPeriod($branchId, $date->copy()->startOfDay(), $date->copy()->endOfDay()),
            'bookings' => Booking::query()->where('branch_id', $branchId)->whereDate('scheduled_at', $date)->count(),
            'completed_jobs' => JobCard::query()->where('branch_id', $branchId)->where('status', JobCardStatus::Completed)->whereDate('completed_at', $date)->count(),
        ];
    }

    public function weekly(?int $branchId = null, ?Carbon $date = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $date = $date ?? now();
        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        return [
            'period' => [$start->toDateString(), $end->toDateString()],
            'revenue' => $this->revenueForPeriod($branchId, $start, $end),
            'bookings' => Booking::query()->where('branch_id', $branchId)->whereBetween('scheduled_at', [$start, $end])->count(),
            'completed_jobs' => JobCard::query()->where('branch_id', $branchId)->where('status', JobCardStatus::Completed)->whereBetween('completed_at', [$start, $end])->count(),
        ];
    }

    public function monthly(?int $branchId = null, ?Carbon $date = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $date = $date ?? now();

        return [
            'month' => $date->format('F Y'),
            'revenue' => Invoice::query()
                ->where('branch_id', $branchId)
                ->whereYear('issued_at', $date->year)
                ->whereMonth('issued_at', $date->month)
                ->sum('paid_amount'),
            'bookings' => Booking::query()
                ->where('branch_id', $branchId)
                ->whereYear('scheduled_at', $date->year)
                ->whereMonth('scheduled_at', $date->month)
                ->count(),
            'new_customers' => Customer::query()
                ->where('branch_id', $branchId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count(),
        ];
    }

    public function revenue(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        return [
            'today' => $this->revenueForPeriod($branchId, now()->startOfDay(), now()->endOfDay()),
            'this_week' => $this->revenueForPeriod($branchId, now()->startOfWeek(), now()->endOfWeek()),
            'this_month' => Invoice::query()
                ->where('branch_id', $branchId)
                ->whereYear('issued_at', now()->year)
                ->whereMonth('issued_at', now()->month)
                ->sum('paid_amount'),
            'outstanding' => Invoice::query()
                ->where('branch_id', $branchId)
                ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid])
                ->sum('balance_due'),
        ];
    }

    public function customers(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        return [
            'total' => Customer::query()->where('branch_id', $branchId)->count(),
            'new_this_month' => Customer::query()
                ->where('branch_id', $branchId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'top_spenders' => Customer::query()
                ->where('branch_id', $branchId)
                ->orderByDesc('lifetime_spending')
                ->limit(10)
                ->get(),
        ];
    }

    public function staff(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        return [
            'employees' => Employee::query()->where('branch_id', $branchId)->where('is_active', true)->count(),
            'jobs_completed' => JobCard::query()
                ->where('branch_id', $branchId)
                ->where('status', JobCardStatus::Completed)
                ->whereMonth('completed_at', now()->month)
                ->count(),
        ];
    }

    public function inventory(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        return [
            'total_products' => Product::query()->where('branch_id', $branchId)->where('is_active', true)->count(),
            'low_stock' => Product::query()
                ->where('branch_id', $branchId)
                ->whereColumn('quantity_on_hand', '<=', 'minimum_level')
                ->count(),
            'stock_value' => Product::query()
                ->where('branch_id', $branchId)
                ->selectRaw('SUM(quantity_on_hand * cost_price) as value')
                ->value('value') ?? 0,
        ];
    }

    protected function revenueForPeriod(?int $branchId, Carbon $start, Carbon $end): float
    {
        return (float) Invoice::query()
            ->where('branch_id', $branchId)
            ->whereBetween('issued_at', [$start, $end])
            ->sum('paid_amount');
    }
}
