<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_super_admin_has_all_permissions_via_gate(): void
    {
        $role = Role::where('slug', RoleSlug::SuperAdmin->value)->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->can('viewAny', Branch::class));
    }

    public function test_cashier_has_limited_permissions(): void
    {
        $role = Role::where('slug', RoleSlug::Cashier->value)->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasPermission('pos.access'));
        $this->assertFalse($user->hasPermission('branches.delete'));
    }

    public function test_role_permissions_can_be_updated_dynamically_from_role_management(): void
    {
        $branch = Branch::query()->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleSlug::SuperAdmin->value)->firstOrFail();
        $cashierRole = Role::query()->where('slug', RoleSlug::Cashier->value)->firstOrFail();
        $inventoryViewPermission = Permission::query()->where('slug', 'inventory.view')->firstOrFail();

        $admin = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($adminRole);

        $response = $this->actingAs($admin)->put(route('settings.roles.update', $cashierRole), [
            'name' => $cashierRole->name,
            'description' => $cashierRole->description,
            'permissions' => $cashierRole->permissions->pluck('id')->push($inventoryViewPermission->id)->all(),
        ]);

        $response->assertRedirect(route('settings.roles.index'));

        $cashierRole->refresh();
        $cashierRole->load('permissions');

        $this->assertTrue($cashierRole->permissions->pluck('slug')->contains('inventory.view'));
    }
}
