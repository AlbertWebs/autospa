<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Models\Expense;
use App\Models\FinanceAccountClosure;
use App\Services\BranchService;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    use AssignsBranchId;

    public function __construct(
        protected FinanceService $financeService,
        protected BranchService $branchService,
    ) {}

    public function index(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $branchId = $this->branchService->currentBranchId();

        return view('finance.index', [
            'report' => $this->financeService->profitLoss($branchId, $from, $to),
            'closures' => FinanceAccountClosure::query()
                ->with('closer')
                ->where('branch_id', $branchId)
                ->latest('closed_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function income(Request $request): View
    {
        [$from, $to] = $this->period($request);

        return view('finance.income', [
            'report' => $this->financeService->income(
                $this->branchService->currentBranchId(),
                $from,
                $to,
            ),
        ]);
    }

    public function expenses(Request $request): View
    {
        [$from, $to] = $this->period($request);

        return view('finance.expenses', [
            'report' => $this->financeService->expenses(
                $this->branchService->currentBranchId(),
                $from,
                $to,
            ),
        ]);
    }

    public function profitLoss(Request $request): View
    {
        [$from, $to] = $this->period($request);

        return view('finance.profit-loss', [
            'report' => $this->financeService->profitLoss(
                $this->branchService->currentBranchId(),
                $from,
                $to,
            ),
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_on' => ['required', 'date'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $payload = $this->withBranchId([
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'spent_on' => $validated['spent_on'],
            'created_by' => $request->user()?->id,
        ]);

        if (empty($payload['branch_id'])) {
            return back()
                ->withInput()
                ->with('error', 'Select a branch before recording an expense.');
        }

        Expense::query()->create($payload);

        [$from, $to] = $this->periodIncluding(
            $validated['spent_on'],
            $request->date('from'),
            $request->date('to'),
        );

        return redirect()->route('finance.expenses', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ])->with('success', 'Expense recorded successfully.');
    }

    public function closeAccounts(Request $request): RedirectResponse
    {
        [$from, $to] = $this->period($request);
        $branchId = $this->branchService->currentBranchId();

        $closure = $this->financeService->closeAccounts(
            $branchId,
            $from,
            $to,
            $request->user()?->id,
        );

        return redirect()->route('finance.index', [
            'from' => $closure->from_date?->toDateString(),
            'to' => $closure->to_date?->toDateString(),
        ])->with('success', 'Accounts closed for selected period.');
    }

    protected function period(Request $request): array
    {
        $from = $request->date('from')?->startOfDay() ?? now()->copy()->startOfMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->copy()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /**
     * Keep the active report window, but always expand it so the saved expense is visible.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function periodIncluding(string $spentOn, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $spentOn = Carbon::parse($spentOn)->startOfDay();
        $from = $from?->copy()->startOfDay() ?? $spentOn->copy()->startOfMonth();
        $to = $to?->copy()->endOfDay() ?? now()->copy()->endOfDay();

        if ($spentOn->lt($from)) {
            $from = $spentOn->copy()->startOfDay();
        }

        if ($spentOn->gt($to)) {
            $to = $spentOn->copy()->endOfDay();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
