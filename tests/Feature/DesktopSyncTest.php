<?php

namespace Tests\Feature;

use App\Enums\JobCardStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\PaymentMethod;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DesktopSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class, SettingSeeder::class]);
    }

    public function test_push_applies_customer_create_mutation_with_desktop_header(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $mutationId = (string) Str::uuid();
        $customerUuid = (string) Str::uuid();

        $response = $this->actingAs($user)
            ->withHeader('X-AutoSpa-Client', 'electron')
            ->postJson(route('desktop.sync.push'), [
                'mutations' => [[
                    'id' => $mutationId,
                    'type' => 'customer.create',
                    'client_entity_uuid' => $customerUuid,
                    'payload' => [
                        'uuid' => $customerUuid,
                        'full_name' => 'Offline Customer',
                        'phone' => '0700111222',
                    ],
                    'created_at' => now()->toIso8601String(),
                ]],
            ]);

        $response->assertOk();
        $response->assertJsonPath('results.0.status', 'applied');
        $response->assertJsonPath('results.0.customer.full_name', 'Offline Customer');

        $this->assertDatabaseHas('customers', [
            'uuid' => $customerUuid,
            'full_name' => 'Offline Customer',
        ]);
    }

    public function test_push_returns_duplicate_for_replayed_mutation(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $mutationId = (string) Str::uuid();
        $customerUuid = (string) Str::uuid();

        $payload = [
            'mutations' => [[
                'id' => $mutationId,
                'type' => 'customer.create',
                'client_entity_uuid' => $customerUuid,
                'payload' => [
                    'uuid' => $customerUuid,
                    'full_name' => 'Replay Customer',
                    'phone' => '0700333444',
                ],
                'created_at' => now()->toIso8601String(),
            ]],
        ];

        $this->actingAs($user)
            ->withHeader('X-AutoSpa-Client', 'electron')
            ->postJson(route('desktop.sync.push'), $payload)
            ->assertOk();

        $response = $this->actingAs($user)
            ->withHeader('X-AutoSpa-Client', 'electron')
            ->postJson(route('desktop.sync.push'), $payload);

        $response->assertOk();
        $response->assertJsonPath('results.0.status', 'duplicate');
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_push_resolves_dependencies_in_order(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        $category = ServiceCategory::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Wash',
            'is_active' => true,
        ]);
        $service = Service::query()->create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Body Wash',
            'price' => 400,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
        $customerUuid = (string) Str::uuid();
        $vehicleUuid = (string) Str::uuid();
        $jobCardUuid = (string) Str::uuid();

        $response = $this->actingAs($user)
            ->withHeader('X-AutoSpa-Client', 'electron')
            ->postJson(route('desktop.sync.push'), [
                'mutations' => [
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'customer.create',
                        'client_entity_uuid' => $customerUuid,
                        'payload' => [
                            'uuid' => $customerUuid,
                            'full_name' => 'Chain Customer',
                            'phone' => '0700555666',
                        ],
                        'created_at' => now()->toIso8601String(),
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'vehicle.create',
                        'client_entity_uuid' => $vehicleUuid,
                        'payload' => [
                            'uuid' => $vehicleUuid,
                            'customer_id' => "client:{$customerUuid}",
                            'registration_number' => 'KAA 123A',
                        ],
                        'created_at' => now()->toIso8601String(),
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'job_card.create',
                        'client_entity_uuid' => $jobCardUuid,
                        'payload' => [
                            'uuid' => $jobCardUuid,
                            'customer_id' => "client:{$customerUuid}",
                            'vehicle_id' => "client:{$vehicleUuid}",
                            'status' => JobCardStatus::Open->value,
                            'service_ids' => [$service->id],
                        ],
                        'created_at' => now()->toIso8601String(),
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertJsonPath('results.0.status', 'applied');
        $response->assertJsonPath('results.1.status', 'applied');
        $response->assertJsonPath('results.2.status', 'applied');

        $customer = Customer::query()->where('uuid', $customerUuid)->firstOrFail();
        $vehicle = Vehicle::query()->where('uuid', $vehicleUuid)->firstOrFail();
        $jobCard = JobCard::query()->where('uuid', $jobCardUuid)->firstOrFail();

        $this->assertSame($customer->id, $vehicle->customer_id);
        $this->assertSame($customer->id, $jobCard->customer_id);
        $this->assertSame($vehicle->id, $jobCard->vehicle_id);
    }

    public function test_push_applies_pos_checkout_mutation(): void
    {
        $branch = Branch::query()->firstOrFail();
        $user = $this->makeUserWithRole(RoleSlug::Manager, $branch);
        $paymentMethod = PaymentMethod::query()->where('slug', 'cash')->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);

        $category = ServiceCategory::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Express',
            'is_active' => true,
        ]);

        $service = Service::query()->create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Quick Wash',
            'price' => 900,
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withHeader('X-AutoSpa-Client', 'electron')
            ->postJson(route('desktop.sync.push'), [
                'mutations' => [[
                    'id' => (string) Str::uuid(),
                    'type' => 'pos.checkout',
                    'payload' => [
                        'customer_id' => $customer->id,
                        'payment_method_id' => $paymentMethod->id,
                        'method' => 'cash',
                        'subtotal' => '900.00',
                        'discount_amount' => '0',
                        'tax_amount' => '0',
                        'total_amount' => '900.00',
                        'items' => [[
                            'item_type' => 'service',
                            'item_id' => $service->id,
                            'description' => $service->name,
                            'quantity' => 1,
                            'unit_price' => '900.00',
                            'total' => '900.00',
                        ]],
                    ],
                    'created_at' => now()->toIso8601String(),
                ]],
            ]);

        $response->assertOk();
        $response->assertJsonPath('results.0.status', 'applied');

        $receipt = Receipt::query()->firstOrFail();
        $this->assertSame(900.0, (float) $receipt->amount);
    }

    public function test_push_rejects_mpesa_checkout_mutation(): void
    {
        $branch = Branch::query()->firstOrFail();
        $user = $this->makeUserWithRole(RoleSlug::Manager, $branch);
        $paymentMethod = PaymentMethod::query()->where('slug', 'mpesa')->firstOrFail();
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->withHeader('X-AutoSpa-Client', 'electron')
            ->postJson(route('desktop.sync.push'), [
                'mutations' => [[
                    'id' => (string) Str::uuid(),
                    'type' => 'pos.checkout',
                    'payload' => [
                        'customer_id' => $customer->id,
                        'payment_method_id' => $paymentMethod->id,
                        'method' => 'mpesa',
                        'subtotal' => '500.00',
                        'discount_amount' => '0',
                        'tax_amount' => '0',
                        'total_amount' => '500.00',
                        'items' => [[
                            'item_type' => 'service',
                            'item_id' => null,
                            'description' => 'Offline M-Pesa',
                            'quantity' => 1,
                            'unit_price' => '500.00',
                            'total' => '500.00',
                        ]],
                    ],
                    'created_at' => now()->toIso8601String(),
                ]],
            ]);

        $response->assertOk();
        $response->assertJsonPath('results.0.status', 'failed');
        $response->assertJsonPath('results.0.error', 'M-Pesa checkout cannot be synced offline.');
        $this->assertDatabaseCount('receipts', 0);
    }

    public function test_guest_cannot_push_mutations(): void
    {
        $this->postJson(route('desktop.sync.push'), [
            'mutations' => [[
                'id' => (string) Str::uuid(),
                'type' => 'customer.create',
                'payload' => ['full_name' => 'Guest'],
                'created_at' => now()->toIso8601String(),
            ]],
        ])->assertUnauthorized();
    }

    public function test_push_requires_desktop_client_header(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $this->actingAs($user)->postJson(route('desktop.sync.push'), [
            'mutations' => [[
                'id' => (string) Str::uuid(),
                'type' => 'customer.create',
                'client_entity_uuid' => (string) Str::uuid(),
                'payload' => [
                    'uuid' => (string) Str::uuid(),
                    'full_name' => 'Blocked',
                    'phone' => '0700111222',
                ],
                'created_at' => now()->toIso8601String(),
            ]],
        ])->assertForbidden();
    }

    protected function makeUserWithRole(RoleSlug $roleSlug, ?Branch $branch = null): User
    {
        $branch ??= Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', $roleSlug->value)->firstOrFail();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        return $user;
    }
}
