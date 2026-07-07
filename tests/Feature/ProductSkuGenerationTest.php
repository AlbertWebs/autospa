<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_sku_is_auto_generated_on_create(): void
    {
        $branch = Branch::query()->firstOrFail();
        $expected = Product::generateSku($branch->id);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Test Wax',
            'cost_price' => 100,
            'selling_price' => 150,
            'is_active' => true,
        ]);

        $this->assertSame($expected, $product->sku);
        $this->assertMatchesRegularExpression('/^SKU-\d{4}$/', $product->sku);
    }

    public function test_skus_increment_sequentially_per_branch(): void
    {
        $branch = Branch::query()->firstOrFail();

        Product::create([
            'branch_id' => $branch->id,
            'name' => 'First',
            'sku' => 'SKU-0005',
            'cost_price' => 10,
            'selling_price' => 20,
            'is_active' => true,
        ]);

        $second = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Second',
            'cost_price' => 10,
            'selling_price' => 20,
            'is_active' => true,
        ]);

        $this->assertSame('SKU-0006', $second->sku);
    }

    public function test_manager_can_create_product_without_entering_sku(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        session(['current_branch_id' => $branch->id]);
        $expected = Product::generateSku($branch->id);

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Ceramic Coating Kit',
            'cost_price' => 2500,
            'selling_price' => 4500,
            'quantity_on_hand' => 10,
            'minimum_level' => 2,
            'is_active' => true,
        ]);

        $product = Product::query()->where('name', 'Ceramic Coating Kit')->firstOrFail();

        $response->assertRedirect(route('products.show', $product));
        $response->assertSessionHas('success');
        $this->assertSame($expected, $product->sku);
        $this->assertSame($branch->id, $product->branch_id);
    }

    public function test_create_page_shows_next_sku_preview(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        session(['current_branch_id' => $branch->id]);

        Product::create([
            'branch_id' => $branch->id,
            'name' => 'Existing',
            'sku' => 'SKU-0099',
            'cost_price' => 10,
            'selling_price' => 20,
            'is_active' => true,
        ]);

        $expected = Product::generateSku($branch->id);

        $response = $this->actingAs($user)->get(route('products.create'));

        $response->assertOk();
        $response->assertSee($expected);
        $response->assertSee('Assigned automatically when you save.');
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
