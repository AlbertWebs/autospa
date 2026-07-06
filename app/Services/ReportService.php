<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Attendance;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\JobCardStatus;
use App\Services\StockMovementService;
use App\Support\AttendanceSettings;
use App\Support\CommissionSettings;
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

    public function revenue(?int $branchId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $from = $from ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $to ?? now()->copy()->endOfDay();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'period' => $this->revenueForPeriod($branchId, $from, $to),
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

    public function customers(?int $branchId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $from = $from ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $to ?? now()->copy()->endOfDay();

        $customerBase = fn () => Customer::query()->where('branch_id', $branchId);

        $newInPeriod = $customerBase()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $periodRevenue = (float) Invoice::query()
            ->where('branch_id', $branchId)
            ->whereBetween('issued_at', [$from, $to])
            ->sum('paid_amount');

        $activeFromInvoices = Invoice::query()
            ->where('branch_id', $branchId)
            ->whereBetween('issued_at', [$from, $to])
            ->where('paid_amount', '>', 0)
            ->distinct()
            ->pluck('customer_id');

        $activeFromJobs = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->whereBetween('completed_at', [$from, $to])
            ->distinct()
            ->pluck('customer_id');

        $activeCustomerIds = $activeFromInvoices->merge($activeFromJobs)->unique()->filter()->values();
        $activeInPeriod = $activeCustomerIds->count();

        $repeatFromInvoices = Invoice::query()
            ->where('branch_id', $branchId)
            ->whereBetween('issued_at', [$from, $to])
            ->where('paid_amount', '>', 0)
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('customer_id');

        $repeatFromJobs = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->whereBetween('completed_at', [$from, $to])
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('customer_id');

        $returningInPeriod = $repeatFromInvoices->merge($repeatFromJobs)->unique()->filter()->count();

        $visitCounts = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->selectRaw('customer_id, COUNT(*) as visits')
            ->groupBy('customer_id')
            ->pluck('visits', 'customer_id');

        $customersWithVisits = $visitCounts->count();
        $totalCustomers = $customerBase()->count();

        $acquisitionTrend = collect(range(5, 0))->map(function (int $monthsAgo) use ($branchId, $to) {
            $month = $to->copy()->subMonths($monthsAgo)->startOfMonth();

            return [
                'label' => $month->format('M Y'),
                'count' => Customer::query()
                    ->where('branch_id', $branchId)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        });

        $maxAcquisition = max(1, $acquisitionTrend->max('count') ?? 1);

        $topSpenders = Invoice::query()
            ->where('branch_id', $branchId)
            ->whereBetween('issued_at', [$from, $to])
            ->selectRaw('customer_id, SUM(paid_amount) as period_spending, COUNT(*) as invoice_count')
            ->groupBy('customer_id')
            ->orderByDesc('period_spending')
            ->limit(10)
            ->with('customer')
            ->get()
            ->filter(fn ($row) => $row->customer !== null)
            ->values();

        $newCustomers = $customerBase()
            ->whereBetween('created_at', [$from, $to])
            ->withCount('vehicles')
            ->latest()
            ->limit(15)
            ->get();

        $inactiveCutoff = now()->subDays(60)->endOfDay();

        $atRiskQuery = $customerBase()
            ->whereHas('jobCards', fn ($query) => $query->where('status', JobCardStatus::Completed))
            ->whereDoesntHave('jobCards', fn ($query) => $query
                ->where('status', JobCardStatus::Completed)
                ->where('completed_at', '>=', $inactiveCutoff));

        $atRiskCount = (clone $atRiskQuery)->count();

        $atRiskCustomers = $atRiskQuery
            ->withMax(['jobCards as last_visit_at' => fn ($query) => $query
                ->where('status', JobCardStatus::Completed)], 'completed_at')
            ->withCount(['jobCards as completed_visits' => fn ($query) => $query
                ->where('status', JobCardStatus::Completed)])
            ->orderByDesc('last_visit_at')
            ->limit(10)
            ->get();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total' => $totalCustomers,
            'new_in_period' => $newInPeriod,
            'active_in_period' => $activeInPeriod,
            'period_revenue' => $periodRevenue,
            'avg_spend_per_active' => $activeInPeriod > 0 ? round($periodRevenue / $activeInPeriod, 2) : 0.0,
            'returning_in_period' => $returningInPeriod,
            'repeat_rate' => $activeInPeriod > 0 ? round(($returningInPeriod / $activeInPeriod) * 100, 1) : 0.0,
            'never_visited' => max(0, $totalCustomers - $customersWithVisits),
            'one_time' => $visitCounts->filter(fn (int $visits) => $visits === 1)->count(),
            'regular' => $visitCounts->filter(fn (int $visits) => $visits >= 2 && $visits <= 5)->count(),
            'loyal' => $visitCounts->filter(fn (int $visits) => $visits >= 6)->count(),
            'no_vehicles' => $customerBase()->whereDoesntHave('vehicles')->count(),
            'one_vehicle' => $customerBase()->has('vehicles', '=', 1)->count(),
            'multiple_vehicles' => $customerBase()->has('vehicles', '>', 1)->count(),
            'at_risk_count' => $atRiskCount,
            'acquisition_trend' => $acquisitionTrend,
            'max_acquisition' => $maxAcquisition,
            'top_spenders' => $topSpenders,
            'new_customers' => $newCustomers,
            'at_risk_customers' => $atRiskCustomers,
        ];
    }

    public function staff(?int $branchId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        $from = $from ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $to ?? now()->copy()->endOfDay();

        $employeeBase = fn () => Employee::query()->where('branch_id', $branchId);
        $activeEmployees = $employeeBase()->where('is_active', true)->orderBy('full_name')->get();
        $activeCount = $activeEmployees->count();

        $jobsCompleted = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->whereBetween('completed_at', [$from, $to])
            ->count();

        $jobsInProgress = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::InProgress)
            ->count();

        $unassignedCompleted = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->whereBetween('completed_at', [$from, $to])
            ->whereNull('assigned_to')
            ->count();

        $assignedCompleted = max(0, $jobsCompleted - $unassignedCompleted);

        $periodRevenue = (float) Invoice::query()
            ->where('invoices.branch_id', $branchId)
            ->whereBetween('invoices.issued_at', [$from, $to])
            ->join('job_cards', 'invoices.job_card_id', '=', 'job_cards.id')
            ->whereNotNull('job_cards.assigned_to')
            ->sum('invoices.paid_amount');

        $completedByEmployee = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->whereBetween('completed_at', [$from, $to])
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $inProgressByEmployee = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::InProgress)
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $revenueByEmployee = Invoice::query()
            ->where('invoices.branch_id', $branchId)
            ->whereBetween('invoices.issued_at', [$from, $to])
            ->join('job_cards', 'invoices.job_card_id', '=', 'job_cards.id')
            ->whereNotNull('job_cards.assigned_to')
            ->selectRaw('job_cards.assigned_to as employee_id, SUM(invoices.paid_amount) as revenue')
            ->groupBy('job_cards.assigned_to')
            ->pluck('revenue', 'employee_id');

        $commissionsByEmployee = Commission::query()
            ->where('branch_id', $branchId)
            ->whereBetween('earned_on', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('employee_id, SUM(amount) as total')
            ->groupBy('employee_id')
            ->pluck('total', 'employee_id');

        $totalCommissions = (float) $commissionsByEmployee->sum();

        $completionTimes = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->whereBetween('completed_at', [$from, $to])
            ->whereNotNull('assigned_to')
            ->whereNotNull('started_at')
            ->get(['assigned_to', 'started_at', 'completed_at'])
            ->groupBy('assigned_to')
            ->map(fn ($jobs) => (int) round($jobs->avg(
                fn (JobCard $job) => $job->started_at->diffInMinutes($job->completed_at)
            )));

        $attendanceEnabled = AttendanceSettings::enabled();
        $attendanceByEmployee = collect();

        if ($attendanceEnabled) {
            $attendanceByEmployee = Attendance::query()
                ->where('branch_id', $branchId)
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->where('status', 'present')
                ->selectRaw('employee_id, COUNT(*) as days')
                ->groupBy('employee_id')
                ->pluck('days', 'employee_id');
        }

        $leaderboard = $activeEmployees->map(function (Employee $employee) use (
            $completedByEmployee,
            $inProgressByEmployee,
            $revenueByEmployee,
            $commissionsByEmployee,
            $completionTimes,
            $attendanceByEmployee,
        ) {
            return [
                'employee' => $employee,
                'completed' => (int) ($completedByEmployee[$employee->id] ?? 0),
                'in_progress' => (int) ($inProgressByEmployee[$employee->id] ?? 0),
                'revenue' => (float) ($revenueByEmployee[$employee->id] ?? 0),
                'commissions' => (float) ($commissionsByEmployee[$employee->id] ?? 0),
                'avg_minutes' => $completionTimes[$employee->id] ?? null,
                'attendance_days' => (int) ($attendanceByEmployee[$employee->id] ?? 0),
            ];
        })->sortByDesc('completed')->values();

        $productiveCount = $leaderboard->where('completed', '>', 0)->count();
        $underutilized = $leaderboard->where('completed', 0)->values();

        $statusBreakdown = collect(JobCardStatus::cases())->mapWithKeys(
            fn (JobCardStatus $status) => [
                $status->value => JobCard::query()
                    ->where('branch_id', $branchId)
                    ->forPeriod($from, $to)
                    ->where('status', $status)
                    ->count(),
            ]
        );

        $positionBreakdown = $activeEmployees
            ->groupBy(fn (Employee $employee) => filled($employee->position) ? $employee->position : 'Unspecified')
            ->map(fn ($group, $position) => [
                'position' => $position,
                'count' => $group->count(),
                'completed' => $group->sum(fn (Employee $employee) => (int) ($completedByEmployee[$employee->id] ?? 0)),
            ])
            ->sortByDesc('completed')
            ->values();

        $maxPositionCompleted = max(1, $positionBreakdown->max('completed') ?? 1);

        $productivityTrend = collect(range(3, 0))->map(function (int $weeksAgo) use ($branchId, $to) {
            $start = $to->copy()->subWeeks($weeksAgo)->startOfWeek();
            $end = $to->copy()->subWeeks($weeksAgo)->endOfWeek();

            return [
                'label' => $start->format('M j'),
                'completed' => JobCard::query()
                    ->where('branch_id', $branchId)
                    ->where('status', JobCardStatus::Completed)
                    ->whereBetween('completed_at', [$start, $end])
                    ->count(),
            ];
        });

        $maxWeeklyCompleted = max(1, $productivityTrend->max('completed') ?? 1);

        $avgCompletionMinutes = JobCard::query()
            ->where('branch_id', $branchId)
            ->where('status', JobCardStatus::Completed)
            ->whereBetween('completed_at', [$from, $to])
            ->whereNotNull('started_at')
            ->get(['started_at', 'completed_at'])
            ->avg(fn (JobCard $job) => $job->started_at->diffInMinutes($job->completed_at));

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'active_employees' => $activeCount,
            'inactive_employees' => $employeeBase()->where('is_active', false)->count(),
            'jobs_completed' => $jobsCompleted,
            'jobs_in_progress' => $jobsInProgress,
            'assigned_completed' => $assignedCompleted,
            'unassigned_completed' => $unassignedCompleted,
            'period_revenue' => $periodRevenue,
            'total_commissions' => $totalCommissions,
            'avg_jobs_per_productive' => $productiveCount > 0
                ? round($assignedCompleted / $productiveCount, 1)
                : 0.0,
            'avg_completion_minutes' => $avgCompletionMinutes ? (int) round($avgCompletionMinutes) : null,
            'productive_staff' => $productiveCount,
            'attendance_enabled' => $attendanceEnabled,
            'commissions_enabled' => CommissionSettings::enabled(),
            'status_breakdown' => $statusBreakdown,
            'position_breakdown' => $positionBreakdown,
            'max_position_completed' => $maxPositionCompleted,
            'productivity_trend' => $productivityTrend,
            'max_weekly_completed' => $maxWeeklyCompleted,
            'leaderboard' => $leaderboard,
            'underutilized' => $underutilized,
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
