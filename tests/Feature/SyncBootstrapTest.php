<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncBootstrapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);
    }

    public function test_bootstrap_returns_branch_scoped_reference_data(): void
    {
        $branch = Branch::query()->firstOrFail();
        $user = $this->makeUserWithRole(RoleSlug::Manager, $branch);

        $category = ServiceCategory::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Exterior',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Premium Wash',
            'price' => 2000,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        Product::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Air Freshener',
            'sku' => 'AF-001',
            'selling_price' => 350,
            'cost_price' => 200,
            'is_active' => true,
        ]);

        Customer::factory()->create([
            'branch_id' => $branch->id,
            'full_name' => 'Bootstrap Customer',
        ]);

        $response = $this->actingAs($user)->getJson(route('sync.bootstrap'));

        $response->assertOk();
        $response->assertJsonStructure([
            'branch_id',
            'synced_at',
            'services',
            'products',
            'payment_methods',
            'employees',
            'customers',
            'vehicles',
        ]);
        $response->assertJsonPath('branch_id', $branch->id);
        $response->assertJsonFragment(['name' => 'Premium Wash']);
        $response->assertJsonFragment(['name' => 'Air Freshener']);
        $response->assertJsonFragment(['full_name' => 'Bootstrap Customer']);
        $response->assertJsonFragment(['slug' => 'cash']);
    }

    public function test_guest_cannot_access_bootstrap(): void
    {
        $this->getJson(route('sync.bootstrap'))->assertUnauthorized();
    }

    protected function makeUserWithRole(RoleSlug $roleSlug, ?Branch $branch = null): User
    {
        $branch ??= Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', $roleSlug->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
