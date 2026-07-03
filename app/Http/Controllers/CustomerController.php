<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('customers.index', [
            'customers' => Customer::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($this->withBranchId($request->validated()));

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        return view('customers.show', [
            'customer' => $customer->load(['vehicles', 'bookings']),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted.');
    }

    public function loyalty(): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('customers.loyalty', [
            'transactions' => LoyaltyTransaction::query()
                ->with('customer')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function feedback(): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('customers.feedback', [
            'notes' => CustomerNote::query()
                ->whereHas('customer')
                ->with(['customer', 'user'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
