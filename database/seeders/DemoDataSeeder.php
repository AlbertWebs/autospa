<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\VehicleStatus;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('code', 'HQ')->first();
        if (! $branch) {
            return;
        }

        session(['current_branch_id' => $branch->id]);

        $category = ServiceCategory::updateOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Car Wash'],
            [
                'description' => 'Exterior and interior wash services',
                'sort_order' => 1,
            ]
        );

        $services = [
            ['name' => 'Basic Wash', 'price' => 500, 'duration_minutes' => 30],
            ['name' => 'Executive Wash', 'price' => 1200, 'duration_minutes' => 45],
            ['name' => 'Engine Wash', 'price' => 800, 'duration_minutes' => 40],
            ['name' => 'Interior Detailing', 'price' => 3500, 'duration_minutes' => 120],
            ['name' => 'Exterior Detailing', 'price' => 4000, 'duration_minutes' => 150],
            ['name' => 'Ceramic Coating', 'price' => 15000, 'duration_minutes' => 240],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => $service['name']],
                [
                    'service_category_id' => $category->id,
                    'description' => $service['name'].' service',
                    'price' => $service['price'],
                    'duration_minutes' => $service['duration_minutes'],
                ]
            );
        }

        $supplier = Supplier::updateOrCreate(
            ['branch_id' => $branch->id, 'name' => 'ChemClean Supplies'],
            [
                'contact_person' => 'John Supplier',
                'phone' => '+254711111111',
                'email' => 'orders@chemclean.test',
            ]
        );

        Product::updateOrCreate(
            ['branch_id' => $branch->id, 'sku' => 'SHMP-001'],
            [
                'supplier_id' => $supplier->id,
                'name' => 'Car Shampoo 5L',
                'cost_price' => 800,
                'selling_price' => 1200,
                'quantity_on_hand' => 20,
                'minimum_level' => 5,
            ]
        );

        Product::updateOrCreate(
            ['branch_id' => $branch->id, 'sku' => 'WAX-001'],
            [
                'supplier_id' => $supplier->id,
                'name' => 'Premium Wax',
                'cost_price' => 1500,
                'selling_price' => 2500,
                'quantity_on_hand' => 3,
                'minimum_level' => 5,
            ]
        );

        $customer = Customer::updateOrCreate(
            ['branch_id' => $branch->id, 'phone' => '+254722222222'],
            [
                'full_name' => 'Jane Mwangi',
                'email' => 'jane@example.com',
                'loyalty_points' => 150,
                'total_visits' => 5,
                'lifetime_spending' => 12500,
            ]
        );

        $vehicle = Vehicle::updateOrCreate(
            ['branch_id' => $branch->id, 'registration_number' => 'KDA 123A'],
            [
                'customer_id' => $customer->id,
                'make' => 'Toyota',
                'model' => 'RAV4',
                'year' => 2022,
                'color' => 'Silver',
                'status' => VehicleStatus::Active,
            ]
        );

        $user = User::where('email', 'reception@autospa.test')->first();
        $scheduledAt = now()->addHours(2);

        Booking::updateOrCreate(
            [
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'type' => BookingType::Appointment,
            ],
            [
                'created_by' => $user?->id,
                'status' => BookingStatus::Confirmed,
                'scheduled_at' => $scheduledAt,
                'ends_at' => $scheduledAt->copy()->addHour(),
            ]
        );
    }
}
