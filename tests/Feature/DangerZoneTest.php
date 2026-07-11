<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DangerZoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);
    }

    public function test_super_admin_can_view_danger_zone(): void
    {
        $admin = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $response = $this->actingAs($admin)->get(route('danger-zone.show'));

        $response->assertOk();
        $response->assertSee('Delete test data');
        $response->assertSee('Job cards');
        $response->assertSee('Customers & vehicles');
    }

    public function test_manager_cannot_view_danger_zone(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);

        $this->actingAs($manager)
            ->get(route('danger-zone.show'))
            ->assertForbidden();
    }

    public function test_danger_zone_link_appears_for_super_admin_only(): void
    {
        $admin = $this->makeUserWithRole(RoleSlug::SuperAdmin);
        $manager = $this->makeUserWithRole(RoleSlug::Manager);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Danger Zone', false);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Danger Zone', false);
    }

    public function test_super_admin_can_purge_selected_test_data(): void
    {
        $admin = $this->makeUserWithRole(RoleSlug::SuperAdmin);
        $branch = Branch::query()->firstOrFail();

        Customer::factory()->create(['branch_id' => $branch->id]);
        Expense::query()->create([
            'branch_id' => $branch->id,
            'category' => 'Utilities',
            'description' => 'Test bill',
            'amount' => 1000,
            'spent_on' => now()->toDateString(),
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('danger-zone.destroy'), [
            'groups' => ['customers_vehicles', 'finance'],
            'password' => 'password',
            'confirm' => '1',
        ]);

        $response->assertRedirect(route('danger-zone.show'));
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('expenses', 0);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    public function test_purge_requires_password_and_confirmation(): void
    {
        $admin = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $this->actingAs($admin)
            ->from(route('danger-zone.show'))
            ->delete(route('danger-zone.destroy'), [
                'groups' => ['logs'],
            ])
            ->assertRedirect(route('danger-zone.show'))
            ->assertSessionHasErrors(['password', 'confirm']);
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
