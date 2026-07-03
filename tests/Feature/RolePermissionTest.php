<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
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
}
