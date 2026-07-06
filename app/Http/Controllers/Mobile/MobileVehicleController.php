<?php

namespace App\Http\Controllers\Mobile;

use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\View\View;

class MobileVehicleController extends Controller
{
    public function index(): View
    {
        return view('mobile.vehicles.index', [
            'vehicles' => Vehicle::query()->with('customer')->latest()->paginate(20),
        ]);
    }

    public function active(): View
    {
        return $this->listByStatus(VehicleStatus::Active, 'Active Vehicles');
    }

    public function ready(): View
    {
        return $this->listByStatus(VehicleStatus::ReadyForPickup, 'Ready for Pickup');
    }

    public function show(Vehicle $vehicle): View
    {
        return view('mobile.vehicles.show', [
            'vehicle' => $vehicle->load(['customer', 'jobCards', 'bookings']),
        ]);
    }

    protected function listByStatus(VehicleStatus $status, string $title): View
    {
        return view('mobile.vehicles.list', [
            'title' => $title,
            'vehicles' => Vehicle::query()
                ->with('customer')
                ->where('status', $status)
                ->latest()
                ->paginate(20),
        ]);
    }
}
