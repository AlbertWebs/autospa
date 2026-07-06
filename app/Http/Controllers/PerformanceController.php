<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use App\Support\AttendanceSettings;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function index(): View
    {
        return view('performance.index', [
            'metrics' => PerformanceMetric::query()
                ->with('employee')
                ->latest('period_end')
                ->paginate(20),
            'attendanceEnabled' => AttendanceSettings::enabled(),
        ]);
    }

    public function show(PerformanceMetric $performance): View
    {
        return view('performance.show', [
            'metric' => $performance->load('employee'),
        ]);
    }
}
