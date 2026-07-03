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
        $west = Branch::where('code', 'WEST')->first();

        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@autospa.test', 'role' => RoleSlug::SuperAdmin, 'branch' => null],
            ['name' => 'Branch Manager', 'email' => 'manager@autospa.test', 'role' => RoleSlug::Manager, 'branch' => $hq],
            ['name' => 'Front Desk', 'email' => 'reception@autospa.test', 'role' => RoleSlug::Receptionist, 'branch' => $hq],
            ['name' => 'POS Cashier', 'email' => 'cashier@autospa.test', 'role' => RoleSlug::Cashier, 'branch' => $hq],
            ['name' => 'Lead Detailer', 'email' => 'detailer@autospa.test', 'role' => RoleSlug::Detailer, 'branch' => $hq],
            ['name' => 'Stock Manager', 'email' => 'inventory@autospa.test', 'role' => RoleSlug::InventoryManager, 'branch' => $west],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'branch_id' => $data['branch']?->id,
                    'phone' => '+254700000000',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            $role = Role::where('slug', $data['role']->value)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }
    }
}
