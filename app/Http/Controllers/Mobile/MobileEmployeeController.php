<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\View\View;

class MobileEmployeeController extends Controller
{
    public function index(): View
    {
        return view('mobile.employees.index', [
            'employees' => Employee::query()->where('is_active', true)->latest()->paginate(20),
        ]);
    }
}
