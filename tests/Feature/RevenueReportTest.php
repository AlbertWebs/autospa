<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_revenue_report_with_date_filters(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);

        Invoice::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-1001',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 1500,
            'total_amount' => 1500,
            'paid_amount' => 1500,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-06-15 10:00:00'),
        ]);

        Invoice::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-1002',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 800,
            'total_amount' => 800,
            'paid_amount' => 800,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-07-01 14:00:00'),
        ]);

        $response = $this->actingAs($user)->get(route('reports.revenue', [
            'from' => '2026-06-01',
            'to' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Revenue Report');
        $response->assertSee('Period Revenue');
        $response->assertSee('value="2026-06-01"', false);
        $response->assertSee('value="2026-06-30"', false);
        $response->assertSee('1,500.00');
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
