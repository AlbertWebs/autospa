<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\View\View;

class MobileCommissionController extends Controller
{
    public function index(): View
    {
        return view('mobile.commissions.index', [
            'commissions' => Commission::query()->with('employee')->latest()->paginate(20),
        ]);
    }
}
