<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CommissionService;
use Carbon\Carbon;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);
    }

    public function test_manager_can_view_profit_report_with_money_in_and_out(): void
    {
        Setting::setValue('commission', 'enabled', true);

        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $cashMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();
        $supplier = Supplier::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Chemicals Ltd',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create([
            'branch_id' => $branch->id,
            'full_name' => 'Washer One',
            'employee_type' => \App\Enums\EmployeeType::Attendee,
            'is_active' => true,
        ]);

        $invoice = Invoice::query()->create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-PL-001',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 5000,
            'total_amount' => 5000,
            'paid_amount' => 5000,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-07-05 09:00:00'),
        ]);

        Payment::query()->create([
            'branch_id' => $branch->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_method_id' => $cashMethod->id,
            'method' => PaymentMethodType::Cash,
            'amount' => 5000,
            'status' => 'completed',
            'paid_at' => Carbon::parse('2026-07-05 10:00:00'),
        ]);

        Commission::query()->create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'amount' => 900,
            'rate' => 0.30,
            'status' => CommissionService::STATUS_PAID,
            'earned_on' => '2026-07-04',
            'paid_at' => Carbon::parse('2026-07-05 18:00:00'),
            'payment_method' => CommissionService::PAYMENT_MANUAL,
        ]);

        Commission::query()->create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'amount' => 600,
            'rate' => 0.30,
            'status' => CommissionService::STATUS_PENDING,
            'earned_on' => '2026-07-05',
        ]);

        PurchaseOrder::query()->create([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'reference' => 'PO-100',
            'status' => 'received',
            'total_amount' => 1200,
            'ordered_at' => '2026-07-03',
            'received_at' => '2026-07-05',
        ]);

        $response = $this->actingAs($user)->get(route('reports.profit', [
            'from' => '2026-07-01',
            'to' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertSee('Profit & Loss');
        $response->assertSee('Money In');
        $response->assertSee('Money Out');
        $response->assertSee('Net Profit');
        $response->assertSee('5,000');
        $response->assertSee('2,100');
        $response->assertSee('2,900');
        $response->assertSee('Operating Profit');
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
