<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_super_admin_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_manager_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_can_not_authenticate(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin, [
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_non_admin_and_non_supervisor_users_can_not_authenticate(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Cashier);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Only Admin and Supervisor accounts can sign in.',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    protected function makeUserWithRole(RoleSlug $roleSlug, array $attributes = []): User
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', $roleSlug->value)->firstOrFail();

        $user = User::factory()->create(array_merge([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ], $attributes));

        $user->roles()->attach($role);

        return $user;
    }
}
