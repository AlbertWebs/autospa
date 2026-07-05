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

class JobCardReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_job_cards_report(): void
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
            'status' => JobCardStatus::Completed,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('reports.job-cards'));

        $response->assertOk();
        $response->assertSee('Job Cards Report');
        $response->assertSee('KDJ 902K');
        $response->assertSee('Completed Today');
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
