<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_attendance_routes_are_hidden_when_feature_is_disabled(): void
    {
        Setting::setValue('attendance', 'enabled', false, null, 'boolean');

        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertNotFound();
    }

    public function test_attendance_routes_are_available_when_feature_is_enabled(): void
    {
        Setting::setValue('attendance', 'enabled', true, null, 'boolean');

        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk();
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
