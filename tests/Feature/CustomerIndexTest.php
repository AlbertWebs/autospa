<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_view_customers_index(): void
    {
        $branch = Branch::first();
        $role = Role::where('slug', RoleSlug::Manager->value)->first();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);

        session(['current_branch_id' => $branch->id]);

        Customer::factory()->create(['branch_id' => $branch->id, 'full_name' => 'Test Customer']);

        $response = $this->actingAs($user)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('Test Customer');
    }
}
