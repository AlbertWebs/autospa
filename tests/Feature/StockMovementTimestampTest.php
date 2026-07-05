<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\StockMovementService;
use Carbon\Carbon;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTimestampTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_stock_movements_create_route_is_not_captured_by_show_route(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('stock-movements.create', [
            'type' => 'in',
            'product_id' => 1,
            'return' => 'products',
        ]));

        $response->assertOk();
        $response->assertSee('Add Stock');
    }

    public function test_products_page_includes_add_stock_modal(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        Product::create([
            'branch_id' => $branch->id,
            'sku' => 'MODAL-001',
            'name' => 'Modal Product',
            'unit' => 'pcs',
            'cost_price' => 10,
            'selling_price' => 20,
            'quantity_on_hand' => 4,
            'minimum_level' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('productStockModal');
        $response->assertSee('Add Stock');
        $response->assertDontSee('stock-movements/create');
    }

    public function test_stock_movement_records_moved_at_and_balance_after(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $product = Product::create([
            'branch_id' => $branch->id,
            'sku' => 'WAX-001',
            'name' => 'Car Wax',
            'unit' => 'pcs',
            'cost_price' => 100,
            'selling_price' => 200,
            'quantity_on_hand' => 0,
            'minimum_level' => 5,
            'is_active' => true,
        ]);

        $movedAt = now()->subDays(2)->startOfHour();

        $response = $this->actingAs($user)->post(route('stock-movements.store'), [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'moved_at' => $movedAt->format('Y-m-d\TH:i'),
            'return_to' => 'products',
        ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame(10.0, (float) $product->quantity_on_hand);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'balance_after' => 10,
            'user_id' => $user->id,
        ]);

        $movement = $product->stockMovements()->first();
        $this->assertNotNull($movement->moved_at);
        $this->assertTrue($movement->moved_at->equalTo($movedAt));
    }

    public function test_stock_balance_as_of_uses_movement_timestamps(): void
    {
        $branch = Branch::query()->firstOrFail();
        $service = app(StockMovementService::class);

        $product = Product::create([
            'branch_id' => $branch->id,
            'sku' => 'SOAP-001',
            'name' => 'Car Soap',
            'unit' => 'l',
            'cost_price' => 50,
            'selling_price' => 80,
            'quantity_on_hand' => 0,
            'minimum_level' => 2,
            'is_active' => true,
        ]);

        $service->recordMovement([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'moved_at' => Carbon::parse('2026-07-01 10:00:00'),
            'notes' => null,
        ], null);

        $service->recordMovement([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 3,
            'moved_at' => Carbon::parse('2026-07-03 14:00:00'),
            'notes' => null,
        ], null);

        $product->refresh();

        $this->assertSame(8.0, (float) $product->quantity_on_hand);
        $this->assertSame(5.0, $service->stockBalanceAsOf($product, Carbon::parse('2026-07-02 12:00:00')));
        $this->assertSame(8.0, $service->stockBalanceAsOf($product, Carbon::parse('2026-07-04 09:00:00')));
    }

    public function test_inventory_report_shows_timestamped_movements_and_stock_position(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $service = app(StockMovementService::class);

        $product = Product::create([
            'branch_id' => $branch->id,
            'sku' => 'TOWEL-001',
            'name' => 'Microfiber Towel',
            'unit' => 'pcs',
            'cost_price' => 20,
            'selling_price' => 40,
            'quantity_on_hand' => 0,
            'minimum_level' => 10,
            'is_active' => true,
        ]);

        $service->recordMovement([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 12,
            'moved_at' => Carbon::parse('2026-07-02 09:30:00'),
            'notes' => 'Initial stock',
        ], $user->id);

        $response = $this->actingAs($user)->get(route('reports.inventory', [
            'as_of' => '2026-07-02T23:59',
            'from' => '2026-07-01',
            'to' => '2026-07-05',
        ]));

        $response->assertOk();
        $response->assertSee('Inventory Report');
        $response->assertSee('Microfiber Towel');
        $response->assertSee('Jul 2, 2026 9:30 AM');
        $response->assertSee('Stock Position');
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
