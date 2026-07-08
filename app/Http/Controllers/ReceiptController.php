<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function index(): View
    {
        return view('receipts.index', [
            'receipts' => Receipt::query()
                ->with(['invoice.customer', 'invoice.payments.paymentMethod'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function show(Receipt $receipt): View
    {
        return view('receipts.show', [
            'receipt' => $receipt->load([
                'branch',
                'invoice.customer',
                'invoice.vehicle',
                'invoice.items',
                'invoice.payments.paymentMethod',
            ]),
        ]);
    }
}
