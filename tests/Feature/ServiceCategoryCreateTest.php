<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategoryCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_create_service_category_with_name(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->post(route('services.categories.store'), [
            'name' => 'Premium Wash',
            'description' => 'High-end detailing services',
            'sort_order' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('services.categories.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('service_categories', [
            'name' => 'Premium Wash',
            'description' => 'High-end detailing services',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_create_page_renders_name_input_with_name_attribute(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('services.categories.create'));

        $response->assertOk();
        $response->assertSee('name="name"', false);
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
