<?php

namespace Tests\Feature;

use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCardIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_unified_job_cards_page_with_all_statuses(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDJ 902K',
            'make' => 'Toyota',
            'model' => 'Vitz',
        ]);

        JobCard::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => JobCardStatus::Open,
        ]);

        JobCard::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => JobCardStatus::InProgress,
            'started_at' => now(),
        ]);

        JobCard::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => JobCardStatus::Completed,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('job-cards.index'));

        $response->assertOk();
        $response->assertSee('Open Jobs');
        $response->assertSee('In Progress');
        $response->assertSee('Completed');
        $response->assertSee('KDJ 902K', false);
        $response->assertSee(now()->format('M j, Y'));
    }

    public function test_job_cards_from_previous_days_are_not_shown(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KCA 111A',
            'make' => 'Nissan',
            'model' => 'Note',
        ]);

        $yesterday = now()->subDay();

        $jobCard = JobCard::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => JobCardStatus::Open,
        ]);
        $jobCard->forceFill([
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ])->save();

        $response = $this->actingAs($user)->get(route('job-cards.index'));

        $response->assertOk();
        $response->assertDontSee('KCA 111A');
    }

    public function test_legacy_open_route_redirects_to_job_cards_index(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('job-cards.open'));

        $response->assertRedirect(route('job-cards.index', ['section' => 'open']));
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
