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

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_first_time_user_sees_onboarding_tour_on_dashboard(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Getting started', false);
        $response->assertSee('Skip tour', false);
        $response->assertSee('Welcome to AutoSpa', false);
    }

    public function test_user_who_completed_onboarding_does_not_see_tour(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);
        $user->forceFill(['onboarding_completed_at' => now()])->save();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Skip tour', false);
    }

    public function test_user_can_complete_onboarding(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->postJson(route('onboarding.complete'));

        $response->assertOk();
        $response->assertJson(['message' => 'Welcome tour completed.']);

        $this->assertNotNull($user->fresh()->onboarding_completed_at);
    }

    public function test_user_manual_page_is_available_and_documents_modules(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($user)->get(route('manual.index'));

        $response->assertOk();
        $response->assertSee('User Manual');
        $response->assertSee('Mission Control');
        $response->assertSee('Point of Sale');
        $response->assertSee('Settings');
        $response->assertSee('Start guided tour');
    }

    protected function makeUserWithRole(RoleSlug $roleSlug): User
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', $roleSlug->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'onboarding_completed_at' => null,
        ]);

        $user->roles()->attach($role);

        return $user;
    }
}
