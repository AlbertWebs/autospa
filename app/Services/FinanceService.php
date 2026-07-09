<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Expense;
use App\Models\FinanceAccountClosure;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceService
{
    public function __construct(
        protected BranchService $branchService,
    ) {}

    public function income(?int $branchId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        [$from, $to] = $this->resolvePeriod($from, $to);

        $payments = Payment::query()
            ->with(['customer', 'paymentMethod'])
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('paid_at', [$from, $to])
                    ->orWhere(function ($fallback) use ($from, $to) {
                        $fallback->whereNull('paid_at')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->latest('paid_at')
            ->latest('id')
            ->get();

        $breakdown = $payments
            ->groupBy(fn (Payment $payment) => $payment->paymentMethod?->name ?? ($payment->method?->label() ?? 'Other'))
            ->map(fn (Collection $rows, string $label) => [
                'label' => $label,
                'total' => (float) $rows->sum('amount'),
                'count' => $rows->count(),
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_income' => (float) $payments->sum('amount'),
            'payment_count' => $payments->count(),
            'income_breakdown' => $breakdown,
            'max_income_row' => max(1.0, (float) $breakdown->max('total') ?: 1.0),
            'payments' => $payments,
        ];
    }

    public function expenses(?int $branchId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $branchId = $branchId ?? $this->branchService->currentBranchId();
        [$from, $to] = $this->resolvePeriod($from, $to);

        $manualExpenses = Expense::query()
            ->with('creator')
            ->where('branch_id', $branchId)
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
            ->latest('spent_on')
            ->latest('id')
            ->get();

        $commissionsPaid = (float) Commission::query()
            ->where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $supplierPurchases = (float) PurchaseOrder::query()
            ->where('branch_id', $branchId)
            ->whereNotNull('received_at')
            ->whereBetween('received_at', [$from->toDateString(), $to->toDateString()])
            ->sum('total_amount');

        $manualTotal = (float) $manualExpenses->sum('amount');

        $breakdown = collect([
            ['key' => 'manual_expenses', 'label' => 'Manual expenses', 'total' => $manualTotal],
            ['key' => 'commissions_paid', 'label' => 'Commissions paid', 'total' => $commissionsPaid],
            ['key' => 'supplier_purchases', 'label' => 'Supplier purchases', 'total' => $supplierPurchases],
        ]);

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_expenses' => (float) $breakdown->sum('total'),
            'breakdown' => $breakdown,
            'max_expense_row' => max(1.0, (float) $breakdown->max('total') ?: 1.0),
            'manual_expenses' => $manualExpenses,
            'manual_expenses_total' => $manualTotal,
        ];
    }

    public function profitLoss(?int $branchId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $income = $this->income($branchId, $from, $to);
        $expenses = $this->expenses($branchId, $from, $to);

        return [
            'from' => $income['from'],
            'to' => $income['to'],
            'income_total' => $income['total_income'],
            'expense_total' => $expenses['total_expenses'],
            'net_profit' => (float) $income['total_income'] - (float) $expenses['total_expenses'],
            'income_breakdown' => $income['income_breakdown'],
            'expense_breakdown' => $expenses['breakdown'],
        ];
    }

    public function closeAccounts(int $branchId, Carbon $from, Carbon $to, ?int $userId = null): FinanceAccountClosure
    {
        $summary = $this->profitLoss($branchId, $from, $to);

        return FinanceAccountClosure::query()->create([
            'branch_id' => $branchId,
            'from_date' => $summary['from'],
            'to_date' => $summary['to'],
            'income_total' => $summary['income_total'],
            'expense_total' => $summary['expense_total'],
            'net_profit' => $summary['net_profit'],
            'meta' => [
                'income_breakdown' => $summary['income_breakdown'],
                'expense_breakdown' => $summary['expense_breakdown'],
            ],
            'closed_by' => $userId,
            'closed_at' => now(),
        ]);
    }

    protected function resolvePeriod(?Carbon $from, ?Carbon $to): array
    {
        $from = $from?->copy()->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $to?->copy()->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
