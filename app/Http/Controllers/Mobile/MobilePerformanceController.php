<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PerformanceMetric;
use Illuminate\View\View;

class MobilePerformanceController extends Controller
{
    public function index(): View
    {
        return view('mobile.performance.index', [
            'metrics' => PerformanceMetric::query()->with('employee')->latest()->paginate(20),
        ]);
    }
}
