<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\CommissionService;
use App\Support\CommissionSettings;
use Carbon\Carbon;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_analytical_staff_report(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $employee = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Top Washer',
            'position' => 'Technician',
            'is_active' => true,
        ]);

        $idleEmployee = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Idle Staff',
            'position' => 'Technician',
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDJ 902K',
        ]);

        $jobCard = JobCard::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'assigned_to' => $employee->id,
            'status' => JobCardStatus::Completed,
            'started_at' => Carbon::parse('2026-06-20 09:00:00'),
            'completed_at' => Carbon::parse('2026-06-20 10:30:00'),
        ]);

        Invoice::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'job_card_id' => $jobCard->id,
            'invoice_number' => 'INV-STAFF-1',
            'status' => InvoiceStatus::Paid,
            'subtotal' => 1800,
            'total_amount' => 1800,
            'paid_amount' => 1800,
            'balance_due' => 0,
            'issued_at' => Carbon::parse('2026-06-20 11:00:00'),
        ]);

        Commission::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'reference_type' => $jobCard->getMorphClass(),
            'reference_id' => $jobCard->id,
            'amount' => 540,
            'rate' => 0.30,
            'status' => CommissionService::STATUS_PENDING,
            'earned_on' => '2026-06-20',
            'trigger_event' => CommissionSettings::TRIGGER_JOB_COMPLETED,
        ]);

        $response = $this->actingAs($user)->get(route('reports.staff', [
            'from' => '2026-06-01',
            'to' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Staff Report');
        $response->assertSee('Staff Leaderboard');
        $response->assertSee('Top Washer');
        $response->assertSee('KES 1,800');
        $response->assertSee('Commission Earned');
        $response->assertSee('KES 540');
        $response->assertSee('Underutilized Staff');
        $response->assertSee('Idle Staff');
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
