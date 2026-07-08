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
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\CommissionService;
use App\Services\DashboardService;
use App\Support\CommissionSettings;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPendingPaymentsTest extends TestCase
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

    public function test_pending_payments_includes_unpaid_commissions_and_ordered_purchase_orders(): void
    {
        $branch = Branch::query()->firstOrFail();
        session(['current_branch_id' => $branch->id]);

        $employee = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Washer One',
            'employee_type' => \App\Enums\EmployeeType::Attendee,
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KAA 111A',
        ]);

        $category = ServiceCategory::create([
            'branch_id' => $branch->id,
            'name' => 'Wash',
            'is_active' => true,
        ]);

        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Premium Wash',
            'price' => 1500,
            'is_active' => true,
        ]);

        $jobCard = JobCard::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_to' => $employee->id,
            'status' => JobCardStatus::Completed,
            'completed_at' => now(),
        ]);

        JobCardService::create([
            'job_card_id' => $jobCard->id,
            'service_id' => $service->id,
            'price' => 1500,
        ]);

        app(CommissionService::class)->recordForJobCard($jobCard, CommissionSettings::TRIGGER_JOB_COMPLETED);

        $supplier = Supplier::create([
            'branch_id' => $branch->id,
            'name' => 'Chemicals Ltd',
            'is_active' => true,
        ]);

        PurchaseOrder::create([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'status' => 'ordered',
            'total_amount' => 2500,
        ]);

        PurchaseOrder::create([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'status' => 'received',
            'total_amount' => 999,
        ]);

        $stats = app(DashboardService::class)->stats($branch->id);

        $this->assertEquals(450.0, $stats['pending_commissions']);
        $this->assertEquals(2500.0, $stats['pending_supplier_payments']);
        $this->assertEquals(2950.0, $stats['pending_payments']);
    }

    public function test_dashboard_displays_pending_payments_breakdown(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        Commission::create([
            'employee_id' => Employee::create([
                'branch_id' => $branch->id,
                'full_name' => 'Washer',
                'employee_type' => \App\Enums\EmployeeType::Attendee,
                'is_active' => true,
            ])->id,
            'branch_id' => $branch->id,
            'reference_type' => (new JobCard)->getMorphClass(),
            'reference_id' => 1,
            'amount' => 300,
            'rate' => 0.30,
            'status' => CommissionService::STATUS_PENDING,
            'earned_on' => now()->toDateString(),
            'trigger_event' => CommissionSettings::TRIGGER_JOB_COMPLETED,
        ]);

        $supplier = Supplier::create([
            'branch_id' => $branch->id,
            'name' => 'Parts Co',
            'is_active' => true,
        ]);

        PurchaseOrder::create([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'status' => 'ordered',
            'total_amount' => 1200,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Pending Payments');
        $response->assertSee('KES 1,500');
        $response->assertSee('KES 300 commissions');
        $response->assertSee('KES 1,200 suppliers');
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
