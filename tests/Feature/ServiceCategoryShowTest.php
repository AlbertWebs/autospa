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

class ServiceCategoryShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_service_category_show_page(): void
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::where('slug', RoleSlug::Manager->value)->firstOrFail();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);
        session(['current_branch_id' => $branch->id]);

        $category = ServiceCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Exterior Wash',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('services.categories.show', ['category' => $category]));

        $response->assertOk();
        $response->assertSee('Sort Order');
        $response->assertSee('Edit');
    }
}
