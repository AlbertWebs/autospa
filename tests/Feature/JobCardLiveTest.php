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

class JobCardLiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_live_page_lists_active_wash_jobs(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard] = $this->createJobCardFixtures($manager->branch_id, JobCardStatus::InProgress);

        $response = $this->actingAs($manager)->get(route('job-cards.live'));

        $response->assertOk();
        $response->assertSee('Live');
        $response->assertSee($jobCard->vehicle->registration_number);
        $response->assertSee($jobCard->customer->full_name);
    }

    public function test_live_status_update_marks_job_in_progress_and_sets_started_at(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard] = $this->createJobCardFixtures($manager->branch_id, JobCardStatus::Open);

        $response = $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::InProgress->value,
        ]);

        $response->assertRedirect();

        $jobCard->refresh();

        $this->assertSame(JobCardStatus::InProgress, $jobCard->status);
        $this->assertNotNull($jobCard->started_at);
        $this->assertNull($jobCard->completed_at);
    }

    public function test_live_status_update_returns_json_for_ajax_requests(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard] = $this->createJobCardFixtures($manager->branch_id, JobCardStatus::Open);

        $response = $this->actingAs($manager)->patchJson(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::InProgress->value,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Car washing status updated.',
                'remove_from_live' => false,
                'job_card' => [
                    'id' => $jobCard->id,
                    'status' => JobCardStatus::InProgress->value,
                ],
            ]);
    }

    public function test_cashier_cannot_access_live_page_or_update_live_status(): void
    {
        $cashier = $this->makeUserWithRole(RoleSlug::Cashier);
        [$jobCard] = $this->createJobCardFixtures($cashier->branch_id, JobCardStatus::Open);

        $this->actingAs($cashier)->get(route('job-cards.live'))->assertForbidden();
        $this->actingAs($cashier)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ])->assertForbidden();
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

    protected function createJobCardFixtures(int $branchId, JobCardStatus $status): array
    {
        $customer = Customer::factory()->create([
            'branch_id' => $branchId,
            'full_name' => 'Live Customer',
        ]);

        $vehicle = Vehicle::create([
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'registration_number' => 'KDL 456Z',
            'make' => 'Mazda',
            'model' => 'Demio',
        ]);

        $jobCard = JobCard::create([
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => $status,
        ]);

        return [$jobCard->load(['customer', 'vehicle'])];
    }
}
