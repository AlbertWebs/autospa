<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Product;
use App\Models\StockMovement;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\JobCardStatus;
use App\Services\StockMovementService;
use Carbon\Carbon;

class ReportService
{
    public function __construct(
        protected BranchService $branchService,
        protected StockMovementService $stockMovementService,
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

    public function inventory(
        ?int $branchId = null,
        ?Carbon $asOf = null,
        ?Carbon $from = null,
        ?Carbon $to = null,
    ): array {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $asOf = $asOf ?? now();
        $from = $from ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $to ?? now()->copy()->endOfDay();

        $products = Product::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stockPositions = $products->map(function (Product $product) use ($asOf) {
            $quantity = $this->stockMovementService->stockBalanceAsOf($product, $asOf);

            return [
                'product' => $product,
                'quantity' => $quantity,
                'value' => $quantity * (float) $product->cost_price,
                'is_low' => $quantity <= (float) $product->minimum_level,
            ];
        });

        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->where('branch_id', $branchId)
            ->whereBetween('moved_at', [$from, $to])
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return [
            'as_of' => $asOf->format('Y-m-d\TH:i'),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_products' => $products->count(),
            'low_stock' => $stockPositions->where('is_low', true)->count(),
            'stock_value' => $stockPositions->sum('value'),
            'stock_in_count' => $movements->where('type', 'in')->count(),
            'stock_in_quantity' => $movements->where('type', 'in')->sum('quantity'),
            'movements' => $movements,
            'stock_positions' => $stockPositions,
        ];
    }

    public function jobCards(?int $branchId = null, ?Carbon $date = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $date = $date ?? now();
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $statusCounts = function (Carbon $start, Carbon $end) use ($branchId): array {
            $base = fn () => JobCard::query()
                ->where('branch_id', $branchId)
                ->forPeriod($start, $end);

            return [
                'open' => $base()->where('status', JobCardStatus::Open)->count(),
                'in_progress' => $base()->where('status', JobCardStatus::InProgress)->count(),
                'completed' => $base()->where('status', JobCardStatus::Completed)->count(),
                'cancelled' => $base()->where('status', JobCardStatus::Cancelled)->count(),
                'total' => $base()->count(),
            ];
        };

        return [
            'date' => $date->toDateString(),
            'today' => $statusCounts($date->copy()->startOfDay(), $date->copy()->endOfDay()),
            'week' => $statusCounts($weekStart, $weekEnd),
            'month' => $statusCounts($monthStart, $monthEnd),
            'week_period' => [$weekStart->toDateString(), $weekEnd->toDateString()],
            'month_label' => $date->format('F Y'),
            'job_cards' => JobCard::query()
                ->with(['customer', 'vehicle', 'assignee'])
                ->where('branch_id', $branchId)
                ->forDay($date)
                ->latest()
                ->get(),
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
