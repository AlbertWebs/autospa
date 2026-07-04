<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\PaymentMethod;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);
    }

    public function test_pos_page_shows_global_payment_methods_for_branch_users(): void
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', RoleSlug::Cashier->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('pos.index'));

        $response->assertOk();
        $response->assertSee('Cash');
        $response->assertSee('M-Pesa');
    }

    public function test_cash_checkout_creates_receipt_and_redirects_to_receipt_page(): void
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', RoleSlug::Cashier->value)->firstOrFail();
        $paymentMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $customer = Customer::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $category = ServiceCategory::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Wash Bay',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Exterior Wash',
            'price' => 1500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $response = $this->followingRedirects()->actingAs($user)->post(route('pos.store'), [
            'customer_id' => $customer->id,
            'payment_method_id' => $paymentMethod->id,
            'method' => 'cash',
            'subtotal' => 1500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1500,
            'items' => [[
                'item_type' => 'service',
                'item_id' => $service->id,
                'description' => $service->name,
                'quantity' => 1,
                'unit_price' => 1500,
                'total' => 1500,
            ]],
        ]);

        $receipt = Receipt::query()->firstOrFail();

        $response->assertOk();
        $response->assertSee($receipt->receipt_number);
        $response->assertSee('Print Receipt');
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $receipt->invoice_id,
            'item_type' => 'service',
            'item_id' => $service->id,
            'description' => 'Exterior Wash',
        ]);
        $this->assertDatabaseHas('receipts', [
            'id' => $receipt->id,
            'invoice_id' => $receipt->invoice_id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_pos_only_user_can_complete_cash_sale_and_view_issued_receipt(): void
    {
        $branch = Branch::query()->firstOrFail();
        $paymentMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();
        $permission = Permission::query()->where('slug', 'pos.access')->firstOrFail();

        $role = Role::query()->create([
            'name' => 'POS Only',
            'slug' => 'pos-only',
            'description' => 'Can access point of sale only.',
        ]);
        $role->permissions()->sync([$permission->id]);

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $customer = Customer::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $category = ServiceCategory::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Interior',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Interior Vacuum',
            'price' => 800,
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        $response = $this->followingRedirects()->actingAs($user)->post(route('pos.store'), [
            'customer_id' => $customer->id,
            'payment_method_id' => $paymentMethod->id,
            'method' => 'cash',
            'subtotal' => 800,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 800,
            'items' => [[
                'item_type' => 'service',
                'item_id' => $service->id,
                'description' => $service->name,
                'quantity' => 1,
                'unit_price' => 800,
                'total' => 800,
            ]],
        ]);

        $receipt = Receipt::query()->latest('id')->firstOrFail();

        $response->assertOk();
        $response->assertSee($receipt->receipt_number);
        $response->assertSee('New Sale');
        $response->assertDontSee('All Receipts');
        $response->assertDontSee('View Invoice');
    }

    public function test_cash_checkout_without_items_returns_validation_errors(): void
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', RoleSlug::Cashier->value)->firstOrFail();
        $paymentMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $customer = Customer::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)->from(route('pos.index'))->post(route('pos.store'), [
            'customer_id' => $customer->id,
            'payment_method_id' => $paymentMethod->id,
            'method' => 'cash',
            'subtotal' => 1500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1500,
            'items' => [],
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('receipts', 0);
    }

    public function test_cash_checkout_without_payment_method_returns_validation_errors(): void
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', RoleSlug::Cashier->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $customer = Customer::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $category = ServiceCategory::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Express',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Quick Wash',
            'price' => 500,
            'duration_minutes' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->from(route('pos.index'))->post(route('pos.store'), [
            'customer_id' => $customer->id,
            'payment_method_id' => '',
            'method' => '',
            'subtotal' => 500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 500,
            'items' => [[
                'item_type' => 'service',
                'item_id' => $service->id,
                'description' => $service->name,
                'quantity' => 1,
                'unit_price' => 500,
                'total' => 500,
            ]],
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHasErrors(['payment_method_id', 'method']);
        $this->assertDatabaseCount('receipts', 0);
    }
}
