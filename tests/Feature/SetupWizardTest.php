<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Services\InstallService;
use Database\Seeders\CoreSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupWizardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seedInstalledApplication = false;

    public function test_home_and_login_redirect_to_setup_on_fresh_install(): void
    {
        $this->get('/')->assertRedirect(route('setup.welcome'));
        $this->get(route('login'))->assertRedirect(route('setup.welcome'));
        $this->get(route('dashboard'))->assertRedirect(route('setup.welcome'));
    }

    public function test_setup_welcome_shows_requirements(): void
    {
        $response = $this->get(route('setup.welcome'));

        $response->assertOk();
        $response->assertSee('System requirements');
        $response->assertSee('PHP 8.2+');
    }

    public function test_complete_setup_wizard_creates_business_and_admin(): void
    {
        $this->post(route('setup.welcome.store'))->assertRedirect(route('setup.business'));

        $this->post(route('setup.business.store'), [
            'name' => 'Sparkle Auto Spa',
            'legal_name' => 'Sparkle Auto Spa Ltd',
            'phone' => '+254711111111',
            'email' => 'info@sparkle.test',
            'address' => 'Nairobi, Kenya',
        ])->assertRedirect(route('setup.branch'));

        $this->post(route('setup.branch.store'), [
            'name' => 'Sparkle HQ',
            'code' => 'HQ',
            'phone' => '+254722222222',
            'email' => 'hq@sparkle.test',
        ])->assertRedirect(route('setup.admin'));

        $this->post(route('setup.admin.store'), [
            'name' => 'Setup Admin',
            'email' => 'admin@sparkle.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('setup.team'));

        $this->post(route('setup.team.skip'))->assertRedirect(route('setup.preferences'));

        $this->post(route('setup.preferences.skip'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(app(InstallService::class)->isInstalled());
        $this->assertDatabaseHas('companies', ['name' => 'Sparkle Auto Spa']);
        $this->assertDatabaseHas('branches', ['code' => 'HQ']);
        $this->assertDatabaseHas('users', ['email' => 'admin@sparkle.test']);

        $admin = User::query()->where('email', 'admin@sparkle.test')->firstOrFail();
        $this->assertTrue($admin->roles()->where('slug', RoleSlug::SuperAdmin->value)->exists());
        $this->assertNull($admin->onboarding_completed_at);

        $this->get(route('setup.welcome'))->assertRedirect(route('login'));
    }

    public function test_setup_seeds_car_wash_services_by_default(): void
    {
        $this->post(route('setup.welcome.store'));
        $this->post(route('setup.business.store'), ['name' => 'Catalog Spa']);
        $this->post(route('setup.branch.store'), ['name' => 'Main', 'code' => 'MAIN']);
        $this->post(route('setup.admin.store'), [
            'name' => 'Owner',
            'email' => 'owner@catalogspa.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
        $this->post(route('setup.team.skip'));
        $this->post(route('setup.preferences.skip'))->assertRedirect(route('login'));

        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();

        $this->assertDatabaseHas('services', [
            'branch_id' => $branch->id,
            'name' => 'Engine Wash',
            'price' => 500,
        ]);
        $this->assertSame(10, Service::query()->where('branch_id', $branch->id)->count());
    }

    public function test_setup_can_skip_car_wash_service_catalog(): void
    {
        $this->post(route('setup.welcome.store'));
        $this->post(route('setup.business.store'), ['name' => 'Empty Catalog Spa']);
        $this->post(route('setup.branch.store'), ['name' => 'Main', 'code' => 'EMPTY']);
        $this->post(route('setup.admin.store'), [
            'name' => 'Owner',
            'email' => 'owner@emptyspa.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
        $this->post(route('setup.team.skip'));
        $this->post(route('setup.preferences.store'), [
            'seed_car_wash_services' => false,
        ])->assertRedirect(route('login'));

        $branch = Branch::query()->where('code', 'EMPTY')->firstOrFail();

        $this->assertSame(0, Service::query()->where('branch_id', $branch->id)->count());
    }

    public function test_setup_can_create_optional_team_members(): void
    {
        $this->post(route('setup.welcome.store'));
        $this->post(route('setup.business.store'), ['name' => 'Team Spa']);
        $this->post(route('setup.branch.store'), ['name' => 'Main', 'code' => 'MAIN']);
        $this->post(route('setup.admin.store'), [
            'name' => 'Owner',
            'email' => 'owner@teamspa.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->post(route('setup.team.store'), [
            'create_supervisor' => true,
            'supervisor_name' => 'Floor Supervisor',
            'supervisor_email' => 'supervisor@teamspa.test',
            'supervisor_password' => 'Password123!',
            'supervisor_password_confirmation' => 'Password123!',
        ])->assertRedirect(route('setup.preferences'));

        $this->post(route('setup.preferences.store'), [
            'sms_notifications_enabled' => true,
            'commissions_enabled' => true,
            'commission_default_rate' => 30,
            'commission_trigger' => 'both',
        ])->assertRedirect(route('login'));

        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();

        $supervisor = User::query()->where('email', 'supervisor@teamspa.test')->firstOrFail();
        $this->assertTrue($supervisor->roles()->where('slug', RoleSlug::Manager->value)->exists());
        $this->assertSame($branch->id, $supervisor->branch_id);

        $this->assertTrue(filter_var(Setting::getValue('sms', 'enabled'), FILTER_VALIDATE_BOOLEAN));
        $this->assertTrue(filter_var(Setting::getValue('commission', 'enabled'), FILTER_VALIDATE_BOOLEAN));
        $this->assertEqualsWithDelta(0.30, (float) Setting::getValue('commission', 'default_rate'), 0.001);
        $this->assertTrue(filter_var(Setting::getValue('loyalty', 'enabled'), FILTER_VALIDATE_BOOLEAN));
        $this->assertSame('10', Setting::getValue('loyalty', 'washes_before_free'));
    }

    public function test_seeded_database_is_treated_as_installed(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')->assertRedirect(route('dashboard'));
        $this->get(route('setup.welcome'))->assertRedirect(route('login'));

        $this->assertTrue(Company::query()->exists());
        $this->assertTrue(app(InstallService::class)->isInstalled());
    }

    public function test_core_seeder_is_idempotent_from_welcome_step(): void
    {
        $this->post(route('setup.welcome.store'));
        $this->post(route('setup.welcome.store'));

        $this->assertGreaterThan(0, Role::query()->count());
        $this->assertTrue(Role::query()->where('slug', RoleSlug::SuperAdmin->value)->exists());
    }
}
