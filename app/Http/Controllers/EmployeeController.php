<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Enums\JobCardStatus;
use App\Models\Employee;
use App\Support\AttendanceSettings;
use App\Support\CommissionSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('employees.index', [
            'employees' => Employee::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('employees.create', [
            'nextEmployeeNumber' => Employee::generateEmployeeNumber(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::create($this->withBranchId($request->validated()));

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee created.');
    }

    public function show(Employee $employee): View
    {
        $relations = [
            'user',
            'commissions' => fn ($query) => $query->latest('earned_on')->latest('id')->limit(8),
            'assignedJobCards' => fn ($query) => $query
                ->with(['customer', 'vehicle'])
                ->latest()
                ->limit(8),
        ];

        if (AttendanceSettings::enabled()) {
            $relations['attendance'] = fn ($query) => $query->latest('date')->limit(8);
        }

        $employee->load($relations);

        return view('employees.show', [
            'employee' => $employee,
            'stats' => [
                'active_jobs' => $employee->assignedJobCards()
                    ->whereIn('status', [JobCardStatus::Open, JobCardStatus::InProgress])
                    ->count(),
                'completed_jobs' => $employee->assignedJobCards()
                    ->where('status', JobCardStatus::Completed)
                    ->count(),
                'commission_earned' => (float) $employee->commissions()->sum('amount'),
                'commission_pending' => (float) $employee->commissions()->where('status', 'pending')->sum('amount'),
            ],
            'attendanceEnabled' => AttendanceSettings::enabled(),
            'commissionsEnabled' => CommissionSettings::enabled(),
            'commissionRatePercent' => CommissionSettings::defaultRate() * 100,
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted.');
    }
}
