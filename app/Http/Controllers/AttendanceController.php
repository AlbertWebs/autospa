<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('attendance.index', [
            'attendance' => Attendance::query()
                ->with('employee')
                ->latest('date')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('attendance.create', [
            'employees' => Employee::query()->where('is_active', true)->orderBy('full_name')->get(),
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        Attendance::create($this->withBranchId($request->validated()));

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance recorded.');
    }

    public function show(Attendance $attendance): View
    {
        return view('attendance.show', [
            'attendance' => $attendance->load('employee'),
        ]);
    }
}
