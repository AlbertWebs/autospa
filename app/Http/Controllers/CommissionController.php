<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        return view('commissions.index', [
            'commissions' => Commission::query()
                ->with('employee')
                ->latest('earned_on')
                ->paginate(20),
        ]);
    }

    public function show(Commission $commission): View
    {
        return view('commissions.show', [
            'commission' => $commission->load('employee'),
        ]);
    }
}
