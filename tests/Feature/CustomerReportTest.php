<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_analytical_customer_report(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $loyalCustomer = Customer::factory()->create([
            'branch_id' => $branch->id,
            'full_name' => 'Loyal Customer',
            'created_at' => Carbon::parse('2026-06-10'),
        ]);

        $newCustomer = Customer::factory()->create([
            'branch_id' => $branch->id,
            'full_name' => 'Fresh Customer',
            'created_at' => Carbon::parse('2026-07-02'),
        ]);

        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $loyalCustomer->id,
            'registration_number' => 'KDJ 902K',
        ]);

        JobCard::create([
            'branch_id' => $branch->id,
            'customer_id' => $loyalCustomer->id,
            'vehicle_id' => $vehicle->id,
            'status' => JobCardStatus::Completed,
            'completed_at' => Carbon::parse('2026-06-20 11:00:00'),
        ]);

        Invoice::create([
            'branch_id' => $branch->id,
            'customer_id' => $loyalCustomer->id,
            'invoice_number' => 'INV-CUST-1',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 2500,
            'total_amount' => 2500,
            'paid_amount' => 2500,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-06-21 10:00:00'),
        ]);

        $response = $this->actingAs($user)->get(route('reports.customers', [
            'from' => '2026-06-01',
            'to' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Customer Report');
        $response->assertSee('Top Spenders');
        $response->assertSee('Visit Frequency');
        $response->assertSee('Loyal Customer');
        $response->assertSee('KES 2,500');
        $response->assertDontSee('Fresh Customer');
    }

    protected function makeUserWithRole(RoleSlug $roleSlug): User
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', $roleSlug->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
