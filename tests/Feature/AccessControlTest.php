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

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_dashboard(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_supervisor_role_can_view_pos(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('pos.index'));

        $response->assertOk();
        $response->assertSee('Point of Sale');
    }

    public function test_cashier_cannot_access_user_management_settings(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Cashier);

        $response = $this->actingAs($user)->get(route('settings.users.index'));

        $response->assertForbidden();
    }

    public function test_inventory_manager_cannot_access_pos(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::InventoryManager);

        $response = $this->actingAs($user)->get(route('pos.index'));

        $response->assertForbidden();
    }

    public function test_cashier_cannot_access_staff_module(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Cashier);

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertForbidden();
    }

    public function test_denied_access_shows_reasonable_message(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::InventoryManager);

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertForbidden();
        $response->assertSee('Access denied');
        $response->assertSee('You do not have permission to use this area');
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
