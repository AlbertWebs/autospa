<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::where('code', 'HQ')->first();

        $users = [
            ['name' => 'Admin', 'email' => 'admin@autospa.test', 'role' => RoleSlug::SuperAdmin, 'branch' => null],
            ['name' => 'Branch Supervisor', 'email' => 'supervisor@autospa.test', 'role' => RoleSlug::Manager, 'branch' => $hq, 'pin' => '1234'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'branch_id' => $data['branch']?->id,
                    'phone' => '+254700000000',
                    'password' => Hash::make('password'),
                    'pin' => $data['pin'] ?? null,
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'onboarding_completed_at' => now(),
                ]
            );

            $role = Role::where('slug', $data['role']->value)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }
    }
}
