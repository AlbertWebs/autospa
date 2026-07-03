<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.payment-methods.index', [
            'paymentMethods' => PaymentMethod::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('update', Setting::class);

        return view('settings.payment-methods.create');
    }

    public function store(StorePaymentMethodRequest $request): RedirectResponse
    {
        PaymentMethod::create($this->withBranchId($request->validated()));

        return redirect()->route('settings.payment-methods.index')
            ->with('success', 'Payment method created.');
    }

    public function show(PaymentMethod $paymentMethod): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.payment-methods.show', ['paymentMethod' => $paymentMethod]);
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        $this->authorize('update', Setting::class);

        return view('settings.payment-methods.edit', ['paymentMethod' => $paymentMethod]);
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update($request->validated());

        return redirect()->route('settings.payment-methods.index')
            ->with('success', 'Payment method updated.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $paymentMethod->delete();

        return redirect()->route('settings.payment-methods.index')
            ->with('success', 'Payment method deleted.');
    }
}
