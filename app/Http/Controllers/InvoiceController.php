<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        return view('invoices.index', [
            'invoices' => Invoice::query()
                ->with(['customer', 'vehicle'])
                ->latest('issued_at')
                ->paginate(15),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        return view('invoices.show', [
            'invoice' => $invoice->load(['customer', 'vehicle', 'items', 'payments', 'receipts']),
        ]);
    }
}
