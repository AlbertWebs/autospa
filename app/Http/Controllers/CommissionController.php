<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\User;
use App\Services\BranchService;
use App\Services\CommissionService;
use App\Support\CommissionSettings;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function __construct(
        protected CommissionService $commissionService,
        protected BranchService $branchService,
    ) {}

    public function index(Request $request): View
    {
        $date = Carbon::parse($request->query('date', now()->toDateString()));
        $branchId = $this->branchService->currentBranchId();
        $period = CommissionSettings::periodForDate($date);

        $this->commissionService->syncMissingCommissions($branchId, $date);

        return view('commissions.index', [
            'date' => $date,
            'periodStart' => $period['start'],
            'periodEnd' => $period['end'],
            'periodLabel' => CommissionSettings::periodLabel($period['start'], $period['end']),
            'payoutCycle' => CommissionSettings::payoutCycle(),
            'commissionsEnabled' => CommissionSettings::enabled(),
            'defaultRate' => CommissionSettings::defaultRate(),
            'dailySummary' => $this->commissionService->summary($branchId, $date),
            'totals' => $this->commissionService->totalsForPeriod($branchId, $date),
            'recentCommissions' => Commission::query()
                ->with(['employee', 'reference'])
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->whereDate('earned_on', '>=', $period['start']->toDateString())
                ->whereDate('earned_on', '<=', $period['end']->toDateString())
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function show(Commission $commission): View
    {
        $commission->load(['employee']);

        $jobCard = $commission->reference instanceof JobCard
            ? $commission->reference->loadMissing(['customer', 'vehicle', 'services.service'])
            : null;

        $baseAmount = $commission->rate > 0
            ? round((float) $commission->amount / (float) $commission->rate, 2)
            : null;

        return view('commissions.show', [
            'commission' => $commission,
            'jobCard' => $jobCard,
            'baseAmount' => $baseAmount,
            'triggerLabel' => CommissionSettings::triggerLabel($commission->trigger_event ?? ''),
        ]);
    }

    public function pay(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'send_mpesa' => ['sometimes', 'boolean'],
        ]);

        $employee = \App\Models\Employee::query()->findOrFail($validated['employee_id']);
        $date = Carbon::parse($validated['date']);

        $result = $this->commissionService->payEmployee(
            $employee,
            $date,
            (bool) ($validated['send_mpesa'] ?? false),
        );

        return redirect()
            ->route('commissions.index', ['date' => $date->toDateString()])
            ->with($result['paid'] ? 'success' : 'error', $result['message']);
    }

    public function initiateMpesaPay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
        ]);

        $employee = Employee::query()->findOrFail($validated['employee_id']);
        $result = $this->commissionService->initiateMpesaPayout(
            $employee,
            Carbon::parse($validated['date']),
            $request->user(),
        );

        if (! $result['started']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json($result);
    }

    public function confirmMpesaPay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payout_token' => ['required', 'string', 'uuid'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $result = $this->commissionService->confirmMpesaPayout(
            $validated['payout_token'],
            $validated['otp'],
            $request->user(),
        );

        if (! $result['paid']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json($result);
    }
}
