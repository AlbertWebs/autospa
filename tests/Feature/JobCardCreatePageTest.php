<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCardCreatePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_job_card_create_page(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('job-cards.create'));

        $response->assertOk();
        $response->assertSee('Create Job Card');
        $response->assertSee('Services');
    }

    public function test_creating_a_job_card_requires_at_least_one_service(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $customer = Customer::factory()->create([
            'branch_id' => $user->branch_id,
        ]);

        $vehicle = Vehicle::create([
            'branch_id' => $user->branch_id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDJ 902K',
        ]);

        $response = $this->actingAs($user)->from(route('job-cards.create'))->post(route('job-cards.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'open',
        ]);

        $response->assertRedirect(route('job-cards.create'));
        $response->assertSessionHasErrors('service_ids');
        $this->assertDatabaseCount('job_cards', 0);
    }

    public function test_creating_a_job_card_redirects_to_live_page(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $customer = Customer::factory()->create([
            'branch_id' => $user->branch_id,
        ]);

        $vehicle = Vehicle::create([
            'branch_id' => $user->branch_id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDJ 902K',
            'make' => 'Toyota',
            'model' => 'Vitz',
        ]);

        $service = $this->createService($user->branch_id);

        $response = $this->actingAs($user)->post(route('job-cards.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'open',
            'service_ids' => [$service->id],
        ]);

        $response->assertRedirect(route('job-cards.live'));

        $jobCard = JobCard::query()->firstOrFail();

        $this->assertDatabaseHas('job_card_services', [
            'job_card_id' => $jobCard->id,
            'service_id' => $service->id,
            'price' => 500,
        ]);
    }

    protected function createService(int $branchId): Service
    {
        $category = ServiceCategory::query()->create([
            'branch_id' => $branchId,
            'name' => 'Wash',
            'is_active' => true,
        ]);

        return Service::query()->create([
            'branch_id' => $branchId,
            'service_category_id' => $category->id,
            'name' => 'Body Wash',
            'price' => 500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
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
