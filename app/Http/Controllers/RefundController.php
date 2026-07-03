<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function index(): View
    {
        return view('refunds.index', [
            'refunds' => Refund::query()
                ->with(['invoice.customer', 'processor'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function show(Refund $refund): View
    {
        return view('refunds.show', [
            'refund' => $refund->load(['invoice.customer', 'processor']),
        ]);
    }
}
