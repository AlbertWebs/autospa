<?php

namespace Tests\Feature;

use App\Enums\ActivityEvent;
use App\Enums\EmployeeType;
use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\JobCardService;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ActivityLogService;
use App\Services\CommissionService;
use App\Support\CommissionSettings;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);

        Setting::setValue('commission', 'enabled', true);
        Setting::setValue('commission', 'default_rate', 0.30);
        Setting::setValue('commission', 'trigger', CommissionSettings::TRIGGER_JOB_COMPLETED);
    }

    protected function tearDown(): void
    {
        ActivityLogService::forceFailForTesting(false);
        ActivityLogService::suppress(false);

        parent::tearDown();
    }

    public function test_completing_wash_logs_job_card_and_commission_activity(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $response = $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'event' => 'job_card.updated',
            'branch_id' => $manager->branch_id,
            'user_id' => $manager->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'commission.created',
            'branch_id' => $manager->branch_id,
            'user_id' => $manager->id,
        ]);
    }

    public function test_branch_switch_logs_activity(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::SuperAdmin);
        $branch = Branch::query()->firstOrFail();

        $response = $this->actingAs($user)->post(route('branch.switch'), [
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'event' => ActivityEvent::BranchSwitched->value,
            'branch_id' => $branch->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_login_logs_activity(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->post('/login', [
            'login_method' => 'password',
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('activity_log', [
            'event' => ActivityEvent::AuthLogin->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_sync_push_logs_mutation_activity(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $mutationId = (string) Str::uuid();
        $customerUuid = (string) Str::uuid();

        $response = $this->actingAs($user)->postJson(route('sync.push'), [
            'mutations' => [[
                'id' => $mutationId,
                'type' => 'customer.create',
                'client_entity_uuid' => $customerUuid,
                'payload' => [
                    'uuid' => $customerUuid,
                    'full_name' => 'Logged Customer',
                    'phone' => '0700111222',
                ],
                'created_at' => now()->toIso8601String(),
            ]],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'event' => ActivityEvent::SyncMutationApplied->value,
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
        ]);
    }

    public function test_marking_commission_paid_logs_bulk_payout_activity(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        ActivityLog::query()->delete();

        $response = $this->actingAs($manager)->post(route('commissions.pay'), [
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'event' => ActivityEvent::CommissionPaid->value,
            'branch_id' => $manager->branch_id,
            'user_id' => $manager->id,
        ]);
    }

    public function test_activity_logging_failure_does_not_break_wash_completion(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);

        ActivityLogService::suppress(true);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);
        ActivityLogService::suppress(false);

        $logCountBefore = ActivityLog::query()->count();
        ActivityLogService::forceFailForTesting(true);

        $response = $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('commissions', [
            'employee_id' => $employee->id,
            'reference_id' => $jobCard->id,
            'status' => CommissionService::STATUS_PENDING,
        ]);

        $this->assertEquals($logCountBefore, ActivityLog::query()->count());
    }

    public function test_dashboard_recent_activity_includes_logged_events(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Job card #'.$jobCard->id.' updated', false);
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

    /** @return array{0: JobCard, 1: Employee, 2: Service} */
    protected function createAssignedWash(int $branchId, JobCardStatus $status): array
    {
        $employee = Employee::create([
            'branch_id' => $branchId,
            'full_name' => 'Washer One',
            'employee_type' => EmployeeType::Attendee,
            'phone' => '0700000000',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['branch_id' => $branchId]);

        $vehicle = Vehicle::create([
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'registration_number' => 'KAA 111A',
        ]);

        $category = ServiceCategory::create([
            'branch_id' => $branchId,
            'name' => 'Wash',
            'is_active' => true,
        ]);

        $service = Service::create([
            'branch_id' => $branchId,
            'service_category_id' => $category->id,
            'name' => 'Premium Wash',
            'price' => 1500,
            'is_active' => true,
        ]);

        $jobCard = JobCard::create([
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_to' => $employee->id,
            'status' => $status,
            'started_at' => now(),
            'completed_at' => $status === JobCardStatus::Completed ? now() : null,
        ]);

        JobCardService::create([
            'job_card_id' => $jobCard->id,
            'service_id' => $service->id,
            'price' => $service->price,
            'status' => 'pending',
        ]);

        return [$jobCard->fresh(['customer', 'vehicle']), $employee, $service];
    }
}
