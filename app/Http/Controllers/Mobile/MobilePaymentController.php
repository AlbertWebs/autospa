<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\View\View;

class MobilePaymentController extends Controller
{
    public function index(): View
    {
        return view('mobile.payments.index', [
            'payments' => Payment::query()->with(['invoice', 'paymentMethod'])->latest()->paginate(20),
        ]);
    }

    public function cash(): View
    {
        return $this->byMethod('cash', 'Cash Payments');
    }

    public function mpesa(): View
    {
        return $this->byMethod('mpesa', 'M-Pesa Payments');
    }

    public function card(): View
    {
        return $this->byMethod('card', 'Card Payments');
    }

    public function bank(): View
    {
        return $this->byMethod('bank', 'Bank Payments');
    }

    protected function byMethod(string $slug, string $title): View
    {
        return view('mobile.payments.method', [
            'title' => $title,
            'payments' => Payment::query()
                ->with(['invoice', 'paymentMethod'])
                ->whereHas('paymentMethod', fn ($q) => $q->where('slug', $slug))
                ->latest()
                ->paginate(20),
        ]);
    }
}
