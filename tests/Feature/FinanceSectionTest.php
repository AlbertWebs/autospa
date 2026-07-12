<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethodType;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\FinanceAccountClosure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CommissionService;
use Carbon\Carbon;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);
    }

    public function test_manager_can_view_finance_overview_page(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($manager)->get(route('finance.index'));

        $response->assertOk();
        $response->assertSee('Finance Overview');
        $response->assertSee('Close Accounts');
        $response->assertDontSee('Checkout · Job', false);
    }

    public function test_manager_can_record_manual_expense_from_finance_expenses_page(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $spentOn = '2026-07-08';

        $response = $this->actingAs($manager)->post(route('finance.expenses.store'), [
            'category' => 'Utilities',
            'description' => 'Power bill',
            'amount' => 4500,
            'spent_on' => $spentOn,
            'from' => '2026-07-01',
            'to' => '2026-07-12',
        ]);

        $response->assertRedirect(route('finance.expenses', [
            'from' => '2026-07-01',
            'to' => '2026-07-12',
        ]));
        $this->assertDatabaseHas('expenses', [
            'branch_id' => $branch->id,
            'category' => 'Utilities',
            'description' => 'Power bill',
        ]);

        $this->actingAs($manager)
            ->get(route('finance.expenses', ['from' => '2026-07-01', 'to' => '2026-07-12']))
            ->assertOk()
            ->assertSee('Power bill')
            ->assertSee('Utilities');
    }

    public function test_recorded_expense_outside_current_filter_expands_period_so_it_remains_visible(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($manager)->post(route('finance.expenses.store'), [
            'category' => 'Rent',
            'description' => 'June warehouse rent',
            'amount' => 12000,
            'spent_on' => '2026-06-15',
            'from' => '2026-07-01',
            'to' => '2026-07-12',
        ]);

        $response->assertRedirect(route('finance.expenses', [
            'from' => '2026-06-15',
            'to' => '2026-07-12',
        ]));

        $this->actingAs($manager)
            ->get(route('finance.expenses', ['from' => '2026-06-15', 'to' => '2026-07-12']))
            ->assertOk()
            ->assertSee('June warehouse rent');
    }

    public function test_expenses_form_disables_turbo_drive_for_reliable_full_page_save(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);

        $this->actingAs($manager)
            ->get(route('finance.expenses'))
            ->assertOk()
            ->assertSee('data-turbo="false"', false)
            ->assertSee(route('finance.expenses.store'), false);
    }

    public function test_finance_profit_and_loss_calculates_income_minus_all_expenses_correctly(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $cashMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();
        $supplier = Supplier::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Soap Supply',
            'is_active' => true,
        ]);
        $employee = \App\Models\Employee::query()->create([
            'branch_id' => $branch->id,
            'full_name' => 'Attendant',
            'employee_type' => \App\Enums\EmployeeType::Attendee,
            'is_active' => true,
        ]);

        $invoice = Invoice::query()->create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-FN-001',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 10000,
            'total_amount' => 10000,
            'paid_amount' => 10000,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-07-05 08:00:00'),
        ]);

        Payment::query()->create([
            'branch_id' => $branch->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_method_id' => $cashMethod->id,
            'method' => PaymentMethodType::Cash,
            'amount' => 10000,
            'status' => 'completed',
            'paid_at' => Carbon::parse('2026-07-05 09:00:00'),
        ]);

        Commission::query()->create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'amount' => 1200,
            'rate' => 0.30,
            'status' => CommissionService::STATUS_PAID,
            'earned_on' => '2026-07-04',
            'paid_at' => Carbon::parse('2026-07-06 11:00:00'),
            'payment_method' => CommissionService::PAYMENT_MANUAL,
        ]);

        PurchaseOrder::query()->create([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'reference' => 'PO-FN-100',
            'status' => 'received',
            'total_amount' => 800,
            'ordered_at' => '2026-07-03',
            'received_at' => '2026-07-06',
        ]);

        \App\Models\Expense::query()->create([
            'branch_id' => $branch->id,
            'category' => 'Rent',
            'description' => 'July rent',
            'amount' => 3000,
            'spent_on' => '2026-07-07',
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get(route('finance.profit-loss', [
            'from' => '2026-07-01',
            'to' => '2026-07-31',
        ]));

        $response->assertOk();
        $response->assertSee('Profit &amp; Loss', false);
        $response->assertSee('10,000');
        $response->assertSee('5,000');
        $response->assertSee('5,000');
    }

    public function test_close_accounts_persists_snapshot_with_correct_totals(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $cashMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();

        $invoice = Invoice::query()->create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-FN-002',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 4000,
            'total_amount' => 4000,
            'paid_amount' => 4000,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-07-05 08:00:00'),
        ]);

        Payment::query()->create([
            'branch_id' => $branch->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_method_id' => $cashMethod->id,
            'method' => PaymentMethodType::Cash,
            'amount' => 4000,
            'status' => 'completed',
            'paid_at' => Carbon::parse('2026-07-05 09:00:00'),
        ]);

        \App\Models\Expense::query()->create([
            'branch_id' => $branch->id,
            'category' => 'Utilities',
            'description' => 'Water bill',
            'amount' => 500,
            'spent_on' => '2026-07-05',
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->post(route('finance.close-accounts'), [
            'from' => '2026-07-01',
            'to' => '2026-07-31',
        ]);

        $response->assertRedirect();

        $closure = FinanceAccountClosure::query()->first();
        $this->assertNotNull($closure);
        $this->assertSame('4000.00', number_format((float) $closure->income_total, 2, '.', ''));
        $this->assertSame('500.00', number_format((float) $closure->expense_total, 2, '.', ''));
        $this->assertSame('3500.00', number_format((float) $closure->net_profit, 2, '.', ''));
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
