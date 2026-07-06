<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeNumberGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_employee_number_is_auto_generated_on_create(): void
    {
        $branch = Branch::query()->firstOrFail();
        $expected = Employee::generateEmployeeNumber();

        $employee = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Jane Doe',
            'is_active' => true,
        ]);

        $this->assertSame($expected, $employee->employee_number);
        $this->assertMatchesRegularExpression('/^EMP-\d{4}$/', $employee->employee_number);
    }

    public function test_employee_numbers_increment_sequentially(): void
    {
        $branch = Branch::query()->firstOrFail();

        Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'First',
            'employee_number' => 'EMP-0005',
            'is_active' => true,
        ]);

        $second = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Second',
            'is_active' => true,
        ]);

        $this->assertSame('EMP-0006', $second->employee_number);
    }

    public function test_manager_can_create_employee_without_entering_employee_number(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();
        session(['current_branch_id' => $branch->id]);
        $expected = Employee::generateEmployeeNumber();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'full_name' => 'New Hire',
            'position' => 'Technician',
            'is_active' => true,
        ]);

        $employee = Employee::query()->where('full_name', 'New Hire')->firstOrFail();

        $response->assertRedirect(route('employees.show', $employee));
        $response->assertSessionHas('success');
        $this->assertSame($expected, $employee->employee_number);
        $this->assertSame($branch->id, $employee->branch_id);
    }

    public function test_create_page_shows_next_employee_number_preview(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Existing',
            'employee_number' => 'EMP-0099',
            'is_active' => true,
        ]);

        $expected = Employee::generateEmployeeNumber();

        $response = $this->actingAs($user)->get(route('employees.create'));

        $response->assertOk();
        $response->assertSee($expected);
        $response->assertSee('Assigned automatically when you save.');
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
