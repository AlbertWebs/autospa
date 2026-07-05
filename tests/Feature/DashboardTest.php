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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if ($this->name() === 'test_guest_is_redirected_to_setup') {
            $this->seedInstalledApplication = false;
        }

        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $branch = Branch::first();
        $role = Role::where('slug', RoleSlug::Manager->value)->first();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee("Today's Revenue");
        $response->assertSee('Mission Control');
    }

    public function test_guest_is_redirected_to_setup(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('setup.welcome'));
    }
}
