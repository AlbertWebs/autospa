<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\View\View;

class MobileInvoiceController extends Controller
{
    public function index(): View
    {
        return view('mobile.invoices.index', [
            'invoices' => Invoice::query()->with(['customer', 'vehicle'])->latest('issued_at')->paginate(20),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        return view('mobile.invoices.show', [
            'invoice' => $invoice->load(['customer', 'vehicle', 'payments', 'jobCard']),
        ]);
    }
}
