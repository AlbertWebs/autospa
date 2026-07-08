<?php

namespace App\Http\Controllers;

use App\Enums\VehicleStatus;
use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Services\LoyaltyService;
use App\Support\LoyaltySettings;
use App\Support\RegistrationNumber;
use App\Models\Vehicle;
use App\Services\VehicleSmsNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    use AssignsBranchId;

    public function __construct(
        protected VehicleSmsNotificationService $vehicleSmsNotificationService,
    ) {}

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

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        [$customer, $vehicle] = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $registrationNumber = filled($validated['registration_number'] ?? null)
                ? RegistrationNumber::normalize($validated['registration_number'])
                : null;

            unset($validated['registration_number']);

            $customer = Customer::create($this->withBranchId($validated));

            $vehicle = null;

            if ($registrationNumber) {
                $vehicle = Vehicle::create($this->withBranchId([
                    'customer_id' => $customer->id,
                    'registration_number' => $registrationNumber,
                    'status' => VehicleStatus::Active,
                ]));
            }

            return [$customer, $vehicle];
        });

        if ($vehicle) {
            $this->vehicleSmsNotificationService->sendVehicleRegistered($customer, $vehicle);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Customer created.',
                'customer' => [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'phone' => $customer->phone,
                    'display_name' => $customer->full_name,
                    'vehicle_summary' => $vehicle?->registration_number,
                    'option_label' => $customer->full_name
                        . ($vehicle?->registration_number ? ' · ' . $vehicle->registration_number : ''),
                ],
                'vehicle' => $vehicle ? [
                    'id' => $vehicle->id,
                    'customer_id' => $vehicle->customer_id,
                    'registration_number' => $vehicle->registration_number,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'color' => $vehicle->color,
                ] : null,
            ], 201);
        }

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

    public function loyalty(LoyaltyService $loyaltyService): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('customers.loyalty', [
            'vehicles' => $loyaltyService->paginatedVehicleWashes(request()),
            'search' => request()->input('q', ''),
            'loyaltyEnabled' => LoyaltySettings::enabled(),
            'loyaltySummary' => LoyaltySettings::summary(),
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
