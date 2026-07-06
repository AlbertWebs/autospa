<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use App\Support\AttendanceSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('employees.index', [
            'employees' => Employee::query()->with('user')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('employees.create', [
            'users' => User::query()->where('branch_id', session('current_branch_id'))->orderBy('name')->get(),
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
        $relations = ['user', 'commissions'];

        if (AttendanceSettings::enabled()) {
            $relations[] = 'attendance';
        }

        return view('employees.show', [
            'employee' => $employee->load($relations),
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', [
            'employee' => $employee,
            'users' => User::query()->where('branch_id', session('current_branch_id'))->orderBy('name')->get(),
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
