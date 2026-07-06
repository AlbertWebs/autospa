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

    public function test_only_admin_and_supervisor_roles_exist(): void
    {
        $this->assertSame(2, Role::query()->count());
        $this->assertDatabaseHas('roles', ['slug' => RoleSlug::SuperAdmin->value, 'name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['slug' => RoleSlug::Manager->value, 'name' => 'Supervisor']);
    }

    public function test_super_admin_has_all_permissions_via_gate(): void
    {
        $role = Role::where('slug', RoleSlug::SuperAdmin->value)->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->can('viewAny', Branch::class));
    }

    public function test_supervisor_has_operational_permissions_but_not_branch_delete(): void
    {
        $role = Role::where('slug', RoleSlug::Manager->value)->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasPermission('pos.access'));
        $this->assertTrue($user->hasPermission('inventory.manage'));
        $this->assertFalse($user->hasPermission('branches.delete'));
    }

    public function test_role_permissions_can_be_updated_dynamically_from_role_management(): void
    {
        $branch = Branch::query()->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleSlug::SuperAdmin->value)->firstOrFail();
        $supervisorRole = Role::query()->where('slug', RoleSlug::Manager->value)->firstOrFail();
        $branchDeletePermission = Permission::query()->where('slug', 'branches.delete')->firstOrFail();

        $admin = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($adminRole);

        $response = $this->actingAs($admin)->put(route('settings.roles.update', $supervisorRole), [
            'name' => $supervisorRole->name,
            'description' => $supervisorRole->description,
            'permissions' => $supervisorRole->permissions->pluck('id')->push($branchDeletePermission->id)->all(),
        ]);

        $response->assertRedirect(route('settings.roles.index'));

        $supervisorRole->refresh();
        $supervisorRole->load('permissions');

        $this->assertTrue($supervisorRole->permissions->pluck('slug')->contains('branches.delete'));
    }
}
