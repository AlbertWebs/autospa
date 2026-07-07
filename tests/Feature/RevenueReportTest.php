<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
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
        $response->assertSee('Collected');
        $response->assertSee('Income Summary');
        $response->assertSee('By Payment Method');
        $response->assertSee('By Sales Type');
        $response->assertSee('value="2026-06-01"', false);
        $response->assertSee('value="2026-06-30"', false);
        $response->assertSee('1,500');
    }

    public function test_revenue_report_shows_payment_and_service_breakdown(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $cashMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();

        $invoice = Invoice::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2001',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 2000,
            'total_amount' => 2000,
            'paid_amount' => 2000,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-07-05 11:00:00'),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'service',
            'description' => 'Premium Wash',
            'quantity' => 1,
            'unit_price' => 2000,
            'total' => 2000,
        ]);

        Payment::create([
            'branch_id' => $branch->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_method_id' => $cashMethod->id,
            'method' => PaymentMethodType::Cash->value,
            'amount' => 2000,
            'status' => 'completed',
            'paid_at' => Carbon::parse('2026-07-05 11:05:00'),
        ]);

        $response = $this->actingAs($user)->get(route('reports.revenue', [
            'from' => '2026-07-01',
            'to' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertSee('Cash');
        $response->assertSee('Premium Wash');
        $response->assertSee('Car wash services');
        $response->assertSee('KES 2,000');
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
