<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query()->latest();

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('mobile.customers.index', [
            'customers' => $query->paginate(20)->withQueryString(),
            'search' => $request->input('q'),
        ]);
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        return view('mobile.customers.show', [
            'customer' => $customer->load(['vehicles', 'bookings']),
        ]);
    }

    public function loyalty(): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('mobile.customers.loyalty', [
            'transactions' => LoyaltyTransaction::query()
                ->with('customer')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function feedback(): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('mobile.customers.feedback', [
            'notes' => CustomerNote::query()
                ->whereHas('customer')
                ->with(['customer', 'user'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
