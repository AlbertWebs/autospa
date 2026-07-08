<?php

namespace Tests\Feature;

use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\LoyaltyService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLoyaltyPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_loyalty_page_lists_washed_vehicles_with_counts_and_color_status(): void
    {
        $user = $this->makeManager();
        $branch = Branch::query()->firstOrFail();

        Setting::setValue('loyalty', 'enabled', true);
        Setting::setValue('loyalty', 'washes_before_free', 10);

        $customer = Customer::factory()->create([
            'branch_id' => $branch->id,
            'full_name' => 'Jane Motorist',
        ]);

        $dueVehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KAA 111A',
        ]);

        $progressVehicle = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KBB 222B',
        ]);

        $this->createCompletedWashes($dueVehicle, 10);
        $this->createCompletedWashes($progressVehicle, 5);

        $response = $this->actingAs($user)->get(route('customers.loyalty'));

        $response->assertOk();
        $response->assertSee('Every 10 paid washes earns 1 free wash');
        $response->assertSee('KAA 111A');
        $response->assertSee('KBB 222B');
        $response->assertSee('Jane Motorist');
        $response->assertSee('Free wash next');
        $response->assertSee('5/10 washes');
        $response->assertSee('10');
        $response->assertSee('5');
    }

    public function test_loyalty_service_marks_eleventh_wash_as_new_cycle(): void
    {
        $service = app(LoyaltyService::class);

        $progress = $service->progressForWashCount(11, 10);

        $this->assertSame('cycle_reset', $progress['status']);
        $this->assertSame('slate', $progress['color']);
        $this->assertSame(0, $progress['paid_in_cycle']);
    }

    public function test_loyalty_page_search_filters_by_registration(): void
    {
        $user = $this->makeManager();
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);

        $match = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KCC 333C',
        ]);

        $other = Vehicle::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'registration_number' => 'KDD 444D',
        ]);

        $this->createCompletedWashes($match, 2);
        $this->createCompletedWashes($other, 3);

        $response = $this->actingAs($user)->get(route('customers.loyalty', ['q' => 'KCC']));

        $response->assertOk();
        $response->assertSee('KCC 333C');
        $response->assertDontSee('KDD 444D');
    }

    protected function makeManager(): User
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::where('slug', RoleSlug::Manager->value)->firstOrFail();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);
        session(['current_branch_id' => $branch->id]);

        return $user;
    }

    protected function createCompletedWashes(Vehicle $vehicle, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            JobCard::create([
                'branch_id' => $vehicle->branch_id,
                'customer_id' => $vehicle->customer_id,
                'vehicle_id' => $vehicle->id,
                'status' => JobCardStatus::Completed,
                'completed_at' => now()->subDays($count - $i),
            ]);
        }
    }
}
