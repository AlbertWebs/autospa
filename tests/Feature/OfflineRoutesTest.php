<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\OfflineRoutes;
use App\Support\RouteAccess;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);
    }

    public function test_offline_routes_include_dashboard_and_pos_for_manager(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $urls = OfflineRoutes::urlsForUser($user);

        $this->assertContains(route('dashboard'), $urls);
        $this->assertContains(route('pos.index'), $urls);
        $this->assertContains(route('job-cards.create'), $urls);
        $this->assertContains(route('mobile.pos.index'), $urls);
    }

    public function test_offline_routes_exclude_pos_for_user_without_pos_permission(): void
    {
        $branch = Branch::query()->firstOrFail();
        $permission = Permission::query()->where('slug', 'job-cards.view')->firstOrFail();

        $role = Role::query()->create([
            'name' => 'Job Cards Only',
            'slug' => 'job-cards-only',
            'description' => 'Can view job cards only.',
        ]);
        $role->permissions()->sync([$permission->id]);

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        $urls = OfflineRoutes::urlsForUser($user);

        $this->assertNotContains(route('pos.index'), $urls);
        $this->assertContains(route('job-cards.index'), $urls);
    }

    public function test_syncable_mutations_lists_supported_offline_writes(): void
    {
        $mutations = OfflineRoutes::syncableMutations();

        $this->assertContains('customer.create', $mutations);
        $this->assertContains('job_card.create', $mutations);
        $this->assertContains('pos.checkout', $mutations);
    }

    public function test_operable_menu_includes_pos_and_job_cards_for_manager(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $menu = OfflineRoutes::operableMenuForUser($user);

        $routes = array_column($menu, 'route');

        $this->assertContains('pos.index', $routes);
        $this->assertContains('job-cards.live', $routes);
        $this->assertContains('job-cards.create', $routes);
        $this->assertContains('vehicles.check-in', $routes);
    }

    public function test_operable_menu_excludes_pos_when_disabled(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        \App\Models\Setting::setValue('pos', 'enabled', false, null, 'boolean');

        $menu = OfflineRoutes::operableMenuForUser($user);
        $routes = array_column($menu, 'route');

        $this->assertNotContains('pos.index', $routes);
        $this->assertContains('job-cards.live', $routes);
    }

    public function test_operable_route_names_include_mobile_routes(): void
    {
        $routes = OfflineRoutes::operableRouteNames();

        $this->assertContains('mobile.pos.index', $routes);
        $this->assertContains('mobile.job-cards.live', $routes);
    }

    public function test_route_access_allows_dashboard_for_manager(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $this->assertTrue(app(RouteAccess::class)->allows($user, 'dashboard'));
        $this->assertTrue(app(RouteAccess::class)->allows($user, 'sync.bootstrap'));
    }

    protected function makeUserWithRole(RoleSlug $roleSlug, ?Branch $branch = null): User
    {
        $branch ??= Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', $roleSlug->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
