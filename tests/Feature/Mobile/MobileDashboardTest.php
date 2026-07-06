<?php

namespace Tests\Feature\Mobile;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_guest_is_redirected_from_mobile(): void
    {
        $this->get(route('mobile.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_mobile_dashboard(): void
    {
        $user = $this->managerUser();

        $this->actingAs($user)
            ->get(route('mobile.index'))
            ->assertOk()
            ->assertSee('Mission Control')
            ->assertSee("Today's Revenue");
    }

    public function test_mobile_dashboard_shows_bottom_navigation(): void
    {
        $user = $this->managerUser();

        $this->actingAs($user)
            ->get(route('mobile.index'))
            ->assertOk()
            ->assertSee('Live')
            ->assertSee('Bookings')
            ->assertSee('More');
    }

    public function test_user_without_dashboard_permission_cannot_view_mobile_home(): void
    {
        $branch = Branch::first();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('mobile.index'))
            ->assertForbidden();
    }

    public function test_manager_can_view_mobile_job_cards_live(): void
    {
        $user = $this->managerUser();

        $this->actingAs($user)
            ->get(route('mobile.job-cards.live'))
            ->assertOk()
            ->assertSee('Live');
    }

    public function test_cashier_can_view_mobile_pos(): void
    {
        $branch = Branch::first();
        $role = Role::where('slug', RoleSlug::Cashier->value)->first();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('mobile.pos.index'))
            ->assertOk()
            ->assertSee('Point of Sale');
    }

    protected function managerUser(): User
    {
        $branch = Branch::first();
        $role = Role::where('slug', RoleSlug::Manager->value)->first();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
