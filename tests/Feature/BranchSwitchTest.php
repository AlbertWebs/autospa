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

class BranchSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_super_admin_can_switch_branch(): void
    {
        $role = Role::where('slug', RoleSlug::SuperAdmin->value)->first();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);

        $branch = Branch::first();

        $response = $this->actingAs($user)->post(route('branch.switch'), [
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals($branch->id, session('current_branch_id'));
    }

    public function test_branch_middleware_sets_default_branch(): void
    {
        $branch = Branch::first();
        $role = Role::where('slug', RoleSlug::Manager->value)->first();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $this->actingAs($user)->get(route('dashboard'));

        $this->assertEquals($branch->id, session('current_branch_id'));
    }
}
