<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\View\View;

class MobileAttendanceController extends Controller
{
    public function index(): View
    {
        return view('mobile.attendance.index', [
            'attendance' => Attendance::query()->with('employee')->latest('date')->paginate(20),
        ]);
    }
}
