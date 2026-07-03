<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodType;
use App\Models\Payment;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('payments.index', [
            'payments' => Payment::query()
                ->with(['customer', 'invoice', 'receiver'])
                ->latest('paid_at')
                ->paginate(15),
        ]);
    }

    public function cash(): View
    {
        return $this->listByMethod(PaymentMethodType::Cash, 'payments.cash');
    }

    public function mpesa(): View
    {
        return $this->listByMethod(PaymentMethodType::Mpesa, 'payments.mpesa');
    }

    public function card(): View
    {
        return $this->listByMethod(PaymentMethodType::Card, 'payments.card');
    }

    public function bank(): View
    {
        return $this->listByMethod(PaymentMethodType::Bank, 'payments.bank');
    }

    protected function listByMethod(PaymentMethodType $method, string $view): View
    {
        return view($view, [
            'payments' => Payment::query()
                ->with(['customer', 'invoice'])
                ->where('method', $method)
                ->latest('paid_at')
                ->paginate(15),
        ]);
    }
}
