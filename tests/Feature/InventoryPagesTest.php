<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    /**
     * @dataProvider inventoryIndexRoutes
     */
    public function test_inventory_manager_can_view_inventory_index_pages(string $routeName): void
    {
        $user = $this->makeUserWithRole(RoleSlug::InventoryManager);

        $this->actingAs($user)->get(route($routeName))->assertOk();
    }

    /**
     * @dataProvider inventoryCreateRoutes
     */
    public function test_inventory_manager_can_view_inventory_create_pages(string $routeName, string $expectedText): void
    {
        $user = $this->makeUserWithRole(RoleSlug::InventoryManager);

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertOk();
        $response->assertSee($expectedText);
    }

    public static function inventoryIndexRoutes(): array
    {
        return [
            'products' => ['products.index'],
            'suppliers' => ['suppliers.index'],
            'purchase orders' => ['purchase-orders.index'],
            'stock movements' => ['stock-movements.index'],
            'low stock' => ['products.low-stock'],
        ];
    }

    public static function inventoryCreateRoutes(): array
    {
        return [
            'products' => ['products.create', 'Add Product'],
            'suppliers' => ['suppliers.create', 'Add Supplier'],
            'purchase orders' => ['purchase-orders.create', 'New Purchase Order'],
            'stock movements' => ['stock-movements.create', 'Stock Movement Details'],
        ];
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
