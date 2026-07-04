<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Enums\VehicleStatus;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\VehicleSmsNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends Controller
{
    use AssignsBranchId;

    public function __construct(
        protected VehicleSmsNotificationService $vehicleSmsNotificationService,
    ) {}

    public function index(): View
    {
        return view('vehicles.index', [
            'vehicles' => Vehicle::query()->with('customer')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('vehicles.create', [
            'customers' => Customer::query()->orderBy('full_name')->get(),
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse|JsonResponse
    {
        $vehicle = Vehicle::create($this->withBranchId($request->validated()));
        $vehicle->load('customer');

        if ($vehicle->customer) {
            $this->vehicleSmsNotificationService->sendVehicleRegistered($vehicle->customer, $vehicle);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Vehicle registered.',
                'vehicle' => [
                    'id' => $vehicle->id,
                    'customer_id' => $vehicle->customer_id,
                    'registration_number' => $vehicle->registration_number,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'color' => $vehicle->color,
                ],
            ], 201);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle registered.');
    }

    public function show(Vehicle $vehicle): View
    {
        return view('vehicles.show', [
            'vehicle' => $vehicle->load(['customer', 'jobCards', 'bookings']),
        ]);
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', [
            'vehicle' => $vehicle,
            'customers' => Customer::query()->orderBy('full_name')->get(),
        ]);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $previousStatus = $vehicle->status;
        $vehicle->update($request->validated());
        $vehicle->load('customer');

        if (
            $vehicle->customer
            && $previousStatus !== VehicleStatus::ReadyForPickup
            && $vehicle->status === VehicleStatus::ReadyForPickup
        ) {
            $this->vehicleSmsNotificationService->sendVehicleReadyForCollection($vehicle->customer, $vehicle);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated.');
    }

    public function checkIn(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update(['status' => VehicleStatus::CheckedIn]);

        return back()->with('success', 'Vehicle checked in.');
    }

    public function active(): View
    {
        return $this->listByStatus(VehicleStatus::Active, 'vehicles.active');
    }

    public function ready(): View
    {
        return $this->listByStatus(VehicleStatus::ReadyForPickup, 'vehicles.ready');
    }

    public function history(Vehicle $vehicle): View
    {
        return view('vehicles.history', [
            'vehicle' => $vehicle->load(['jobCards' => fn ($q) => $q->latest(), 'bookings' => fn ($q) => $q->latest()]),
        ]);
    }

    protected function listByStatus(VehicleStatus $status, string $view): View
    {
        return view($view, [
            'vehicles' => Vehicle::query()
                ->with('customer')
                ->where('status', $status)
                ->latest()
                ->paginate(15),
        ]);
    }
}
