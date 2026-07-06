<?php

namespace Tests\Feature;

use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\JobCardService;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
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

    public function test_marking_job_ready_returns_pos_redirect_in_json(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard] = $this->createJobCardFixtures($manager->branch_id, JobCardStatus::InProgress, withService: true);

        $response = $this->actingAs($manager)->patchJson(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response->assertOk()
            ->assertJson([
                'remove_from_live' => true,
                'redirect_to_pos' => route('pos.index', ['job_card' => $jobCard->id]),
            ]);

        $jobCard->refresh();
        $this->assertSame(JobCardStatus::Completed, $jobCard->status);
    }

    public function test_pos_page_preloads_cart_from_job_card(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        session(['current_branch_id' => $manager->branch_id]);
        [$jobCard, , , $service] = $this->createJobCardFixtures($manager->branch_id, JobCardStatus::Completed, withService: true);

        $response = $this->actingAs($manager)->get(route('pos.index', ['job_card' => $jobCard->id]));

        $response->assertOk();
        $response->assertSee('Checkout · Job #'.$jobCard->id);
        $response->assertSee($service->name);
        $response->assertSee('Live Customer');
        $response->assertSee('KDL 456Z');
    }

    public function test_user_without_roles_cannot_access_live_page_or_update_live_status(): void
    {
        $user = User::factory()->create([
            'branch_id' => Branch::query()->firstOrFail()->id,
            'email_verified_at' => now(),
        ]);
        [$jobCard] = $this->createJobCardFixtures($user->branch_id, JobCardStatus::Open);

        $this->actingAs($user)->get(route('job-cards.live'))->assertForbidden();
        $this->actingAs($user)->patch(route('job-cards.live-status', $jobCard), [
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

    protected function createJobCardFixtures(int $branchId, JobCardStatus $status, bool $withService = false): array
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

        $service = null;

        if ($withService) {
            $category = ServiceCategory::create([
                'branch_id' => $branchId,
                'name' => 'Wash',
                'is_active' => true,
            ]);

            $service = Service::create([
                'branch_id' => $branchId,
                'service_category_id' => $category->id,
                'name' => 'Full Body Wash',
                'price' => 1500,
                'is_active' => true,
            ]);

            JobCardService::create([
                'job_card_id' => $jobCard->id,
                'service_id' => $service->id,
                'price' => $service->price,
                'status' => 'pending',
            ]);
        }

        return [$jobCard->load(['customer', 'vehicle', 'services.service']), $customer, $vehicle, $service];
    }
}
