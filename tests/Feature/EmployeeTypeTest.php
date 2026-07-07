<?php

namespace Tests\Feature;

use App\Enums\EmployeeType;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_create_page_shows_employee_type_options_and_no_linked_user_field(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->get(route('employees.create'));

        $response->assertOk();
        $response->assertSee('Employee Type');
        $response->assertSee('Supervisor');
        $response->assertSee('Attendee');
        $response->assertDontSee('Linked User');
    }

    public function test_manager_can_create_supervisor_with_salary(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        session(['current_branch_id' => $user->branch_id]);

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'employee_type' => EmployeeType::Supervisor->value,
            'full_name' => 'Floor Supervisor',
            'phone' => '0711222333',
            'base_salary' => 45000,
            'is_active' => true,
        ]);

        $employee = Employee::query()->where('full_name', 'Floor Supervisor')->firstOrFail();

        $response->assertRedirect(route('employees.show', $employee));
        $this->assertSame(EmployeeType::Supervisor, $employee->employee_type);
        $this->assertSame('45000.00', $employee->base_salary);
        $this->assertSame('Supervisor', $employee->position);
    }

    public function test_manager_can_create_attendee_without_salary(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        session(['current_branch_id' => $user->branch_id]);

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'employee_type' => EmployeeType::Attendee->value,
            'full_name' => 'Bay Attendee',
            'phone' => '0799888777',
            'is_active' => true,
        ]);

        $employee = Employee::query()->where('full_name', 'Bay Attendee')->firstOrFail();

        $response->assertRedirect(route('employees.show', $employee));
        $this->assertSame(EmployeeType::Attendee, $employee->employee_type);
        $this->assertNull($employee->base_salary);
        $this->assertSame('Attendee', $employee->position);
    }

    public function test_supervisor_requires_base_salary(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);

        $response = $this->actingAs($user)->from(route('employees.create'))->post(route('employees.store'), [
            'employee_type' => EmployeeType::Supervisor->value,
            'full_name' => 'Missing Salary',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('employees.create'));
        $response->assertSessionHasErrors('base_salary');
        $this->assertDatabaseMissing('employees', ['full_name' => 'Missing Salary']);
    }

    public function test_only_attendees_are_assignable_to_job_cards(): void
    {
        $branch = Branch::query()->firstOrFail();

        $supervisor = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Shift Supervisor',
            'employee_type' => EmployeeType::Supervisor,
            'base_salary' => 50000,
            'is_active' => true,
        ]);

        $attendee = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Wash Attendee',
            'employee_type' => EmployeeType::Attendee,
            'is_active' => true,
        ]);

        $assignable = Employee::query()->assignableToJobCards($branch->id)->pluck('id');

        $this->assertTrue($assignable->contains($attendee->id));
        $this->assertFalse($assignable->contains($supervisor->id));
    }

    public function test_employee_show_page_displays_profile_and_stats(): void
    {
        $user = $this->makeUserWithRole(RoleSlug::Manager);
        $branch = Branch::query()->firstOrFail();

        $employee = Employee::create([
            'branch_id' => $branch->id,
            'full_name' => 'Profile Attendee',
            'employee_type' => EmployeeType::Attendee,
            'phone' => '0711000000',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('employees.show', $employee));

        $response->assertOk();
        $response->assertSee('Profile Attendee');
        $response->assertSee('Active Jobs');
        $response->assertSee('Recent Job Cards');
        $response->assertSee('Commission per wash');
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
