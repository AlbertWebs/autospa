<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $assignableRoles = [
            RoleSlug::Manager->value,
        ];

        User::query()
            ->where('is_active', true)
            ->whereNotNull('branch_id')
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', $assignableRoles))
            ->with('roles:id,name,slug')
            ->each(function (User $user) {
                Employee::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'branch_id' => $user->branch_id,
                        'full_name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'position' => $user->roles->first()?->name,
                        'hire_date' => now()->toDateString(),
                        'is_active' => true,
                    ],
                );
            });
    }
}
