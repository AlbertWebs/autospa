<?php

namespace Tests\Feature;

use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JobCard;
use App\Models\JobCardService;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\CommissionService;
use App\Support\CommissionSettings;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionWorkflowTest extends TestCase
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

    public function test_completing_wash_creates_commission_for_assigned_washer(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee, $service] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $response = $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('commissions', [
            'employee_id' => $employee->id,
            'reference_type' => $jobCard->getMorphClass(),
            'reference_id' => $jobCard->id,
            'amount' => 450.00,
            'status' => CommissionService::STATUS_PENDING,
        ]);
    }

    public function test_completing_wash_does_not_create_commission_for_supervisor(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee, $service] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);
        $employee->update(['employee_type' => \App\Enums\EmployeeType::Supervisor]);

        $response = $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing('commissions', [
            'employee_id' => $employee->id,
            'reference_id' => $jobCard->id,
        ]);
    }

    public function test_pos_checkout_creates_commission_when_trigger_is_pos_checkout(): void
    {
        Setting::setValue('commission', 'trigger', CommissionSettings::TRIGGER_POS_CHECKOUT);

        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee, $service] = $this->createAssignedWash($manager->branch_id, JobCardStatus::Completed);
        $paymentMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();

        $response = $this->followingRedirects()->actingAs($manager)->post(route('pos.store'), [
            'customer_id' => $jobCard->customer_id,
            'vehicle_id' => $jobCard->vehicle_id,
            'job_card_id' => $jobCard->id,
            'payment_method_id' => $paymentMethod->id,
            'method' => 'cash',
            'subtotal' => 1500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1500,
            'items' => [[
                'item_type' => 'service',
                'item_id' => $service->id,
                'description' => $service->name,
                'quantity' => 1,
                'unit_price' => 1500,
                'total' => 1500,
            ]],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('commissions', [
            'employee_id' => $employee->id,
            'reference_id' => $jobCard->id,
            'amount' => 450.00,
        ]);
    }

    public function test_commissions_page_shows_daily_washer_summary(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response = $this->actingAs($manager)->get(route('commissions.index'));

        $response->assertOk();
        $response->assertSee('Washer payouts');
        $response->assertSee($employee->full_name);
        $response->assertSee('KES 450');
    }

    public function test_manager_can_view_commission_detail_page(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $commission = Commission::query()->where('employee_id', $employee->id)->firstOrFail();

        $response = $this->actingAs($manager)->get(route('commissions.show', $commission));

        $response->assertOk();
        $response->assertSee($employee->full_name);
        $response->assertSee('KES 450');
        $response->assertSee('Linked Wash');
        $response->assertSee('Mark Paid');
    }

    public function test_manager_can_mark_daily_commission_as_paid(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response = $this->actingAs($manager)->post(route('commissions.pay'), [
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('commissions.index', ['date' => now()->toDateString()]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commissions', [
            'employee_id' => $employee->id,
            'status' => CommissionService::STATUS_PAID,
            'payment_method' => CommissionService::PAYMENT_MANUAL,
        ]);
    }

    public function test_manager_can_send_daily_commission_via_mpesa_with_admin_otp(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        $manager->update(['phone' => '0711111111']);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);
        $employee->update(['phone' => '0712345678']);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $initiate = $this->actingAs($manager)->postJson(route('commissions.pay.mpesa.initiate'), [
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        $initiate->assertOk();
        $initiate->assertJsonPath('started', true);
        $payoutToken = $initiate->json('payout_token');
        $this->assertNotEmpty($payoutToken);

        $confirm = $this->actingAs($manager)->postJson(route('commissions.pay.mpesa.confirm'), [
            'payout_token' => $payoutToken,
            'otp' => '123456',
        ]);

        $confirm->assertOk();
        $confirm->assertJsonPath('paid', true);

        $commission = Commission::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->assertSame(CommissionService::STATUS_PAID, $commission->status);
        $this->assertSame(CommissionService::PAYMENT_MPESA, $commission->payment_method);
        $this->assertNotNull($commission->payment_reference);
    }

    public function test_mpesa_payout_rejects_invalid_admin_otp(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        $manager->update(['phone' => '0711111111']);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);
        $employee->update(['phone' => '0712345678']);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $initiate = $this->actingAs($manager)->postJson(route('commissions.pay.mpesa.initiate'), [
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
        ]);

        $confirm = $this->actingAs($manager)->postJson(route('commissions.pay.mpesa.confirm'), [
            'payout_token' => $initiate->json('payout_token'),
            'otp' => '000000',
        ]);

        $confirm->assertStatus(422);

        $this->assertDatabaseHas('commissions', [
            'employee_id' => $employee->id,
            'status' => CommissionService::STATUS_PENDING,
        ]);
    }

    public function test_commissions_index_backfills_missing_commissions_for_completed_washes(): void
    {
        Setting::setValue('commission', 'trigger', CommissionSettings::TRIGGER_JOB_COMPLETED);

        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard, $employee] = $this->createAssignedWash($manager->branch_id, JobCardStatus::Completed);

        $this->assertDatabaseCount('commissions', 0);

        $response = $this->actingAs($manager)->get(route('commissions.index'));

        $response->assertOk();
        $response->assertSee($employee->full_name);
        $response->assertSee('KES 450');

        $this->assertDatabaseHas('commissions', [
            'employee_id' => $employee->id,
            'reference_id' => $jobCard->id,
            'amount' => 450.00,
        ]);
    }

    public function test_dashboard_shows_daily_commission_stats_when_enabled(): void
    {
        $manager = $this->makeUserWithRole(RoleSlug::Manager);
        [$jobCard] = $this->createAssignedWash($manager->branch_id, JobCardStatus::InProgress);

        $this->actingAs($manager)->patch(route('job-cards.live-status', $jobCard), [
            'status' => JobCardStatus::Completed->value,
        ]);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee("Today's Commissions");
        $response->assertSee('Net Profit');
        $response->assertSee('Washers Today');
        $response->assertSee('KES 450');
        $response->assertSee('KES -450');
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
            'employee_type' => \App\Enums\EmployeeType::Attendee,
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
