<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\JobCardStatus;
use App\Support\CommissionSettings;

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

        $todayRevenue = (float) Invoice::query()
            ->where('branch_id', $branchId)
            ->whereDate('issued_at', $today)
            ->whereIn('status', [InvoiceStatus::Paid, InvoiceStatus::PartiallyPaid])
            ->sum('paid_amount');

        return [
            'today_revenue' => $todayRevenue,
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
            ...$this->pendingPaymentStats($branchId),
            'low_stock_count' => Product::query()
                ->where('branch_id', $branchId)
                ->whereColumn('quantity_on_hand', '<=', 'minimum_level')
                ->count(),
            ...$this->commissionStats($branchId, $today, $todayRevenue),
        ];
    }

    /** @return array{pending_payments: float, pending_commissions: float, pending_supplier_payments: float} */
    protected function pendingPaymentStats(int $branchId): array
    {
        $pendingCommissions = CommissionSettings::enabled()
            ? (float) Commission::query()
                ->where('branch_id', $branchId)
                ->where('status', CommissionService::STATUS_PENDING)
                ->sum('amount')
            : 0.0;

        $pendingSupplierPayments = (float) PurchaseOrder::query()
            ->where('branch_id', $branchId)
            ->where('status', 'ordered')
            ->sum('total_amount');

        return [
            'pending_commissions' => $pendingCommissions,
            'pending_supplier_payments' => $pendingSupplierPayments,
            'pending_payments' => $pendingCommissions + $pendingSupplierPayments,
        ];
    }

    protected function commissionStats(int $branchId, string $today, float $todayRevenue): array
    {
        if (! CommissionSettings::enabled()) {
            return [
                'commissions_enabled' => false,
                'today_commissions' => 0,
                'today_commissions_pending' => 0,
                'today_net_profit' => $todayRevenue,
                'today_washers' => 0,
            ];
        }

        $commissionQuery = Commission::query()
            ->where('branch_id', $branchId)
            ->whereDate('earned_on', $today);

        $todayCommissions = (float) (clone $commissionQuery)->sum('amount');

        return [
            'commissions_enabled' => true,
            'today_commissions' => $todayCommissions,
            'today_commissions_pending' => (float) (clone $commissionQuery)->where('status', 'pending')->sum('amount'),
            'today_net_profit' => $todayRevenue - $todayCommissions,
            'today_washers' => (int) JobCard::query()
                ->where('branch_id', $branchId)
                ->where('status', JobCardStatus::Completed)
                ->whereDate('completed_at', $today)
                ->whereNotNull('assigned_to')
                ->distinct('assigned_to')
                ->count('assigned_to'),
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

        return Employee::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->withCount(['assignedJobCards as completed_jobs' => function ($q) {
                $q->where('status', JobCardStatus::Completed)
                    ->whereMonth('completed_at', now()->month);
            }])
            ->orderByDesc('completed_jobs')
            ->limit($limit)
            ->get();
    }

    public function operationsSnapshot(?int $branchId = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();

        if (! $branchId) {
            return [
                'job_cards_open' => 0,
                'job_cards_in_progress' => 0,
                'job_cards_completed' => 0,
                'bookings_pending' => 0,
                'bookings_confirmed' => 0,
            ];
        }

        $today = now()->startOfDay();

        return [
            'job_cards_open' => JobCard::query()
                ->where('branch_id', $branchId)
                ->forDay($today)
                ->where('status', JobCardStatus::Open)
                ->count(),
            'job_cards_in_progress' => JobCard::query()
                ->where('branch_id', $branchId)
                ->forDay($today)
                ->where('status', JobCardStatus::InProgress)
                ->count(),
            'job_cards_completed' => JobCard::query()
                ->where('branch_id', $branchId)
                ->forDay($today)
                ->where('status', JobCardStatus::Completed)
                ->count(),
            'bookings_pending' => Booking::query()
                ->where('branch_id', $branchId)
                ->whereDate('scheduled_at', $today)
                ->where('status', BookingStatus::Pending)
                ->count(),
            'bookings_confirmed' => Booking::query()
                ->where('branch_id', $branchId)
                ->whereDate('scheduled_at', $today)
                ->where('status', BookingStatus::Confirmed)
                ->count(),
        ];
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
            'pending_commissions' => 0,
            'pending_supplier_payments' => 0,
            'low_stock_count' => 0,
            'commissions_enabled' => false,
            'today_commissions' => 0,
            'today_commissions_pending' => 0,
            'today_net_profit' => 0,
            'today_washers' => 0,
        ];
    }
}
