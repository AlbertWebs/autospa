<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'group' => 'dashboard'],
            ['name' => 'View Branches', 'slug' => 'branches.view', 'group' => 'settings'],
            ['name' => 'Create Branches', 'slug' => 'branches.create', 'group' => 'settings'],
            ['name' => 'Update Branches', 'slug' => 'branches.update', 'group' => 'settings'],
            ['name' => 'Delete Branches', 'slug' => 'branches.delete', 'group' => 'settings'],
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group' => 'settings'],
            ['name' => 'Update Settings', 'slug' => 'settings.update', 'group' => 'settings'],
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'settings'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group' => 'settings'],
            ['name' => 'Update Users', 'slug' => 'users.update', 'group' => 'settings'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group' => 'settings'],
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'customers'],
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'group' => 'customers'],
            ['name' => 'Update Customers', 'slug' => 'customers.update', 'group' => 'customers'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'group' => 'customers'],
            ['name' => 'View Vehicles', 'slug' => 'vehicles.view', 'group' => 'vehicles'],
            ['name' => 'Manage Vehicles', 'slug' => 'vehicles.manage', 'group' => 'vehicles'],
            ['name' => 'View Bookings', 'slug' => 'bookings.view', 'group' => 'bookings'],
            ['name' => 'Manage Bookings', 'slug' => 'bookings.manage', 'group' => 'bookings'],
            ['name' => 'View Job Cards', 'slug' => 'job-cards.view', 'group' => 'job-cards'],
            ['name' => 'Manage Job Cards', 'slug' => 'job-cards.manage', 'group' => 'job-cards'],
            ['name' => 'View Services', 'slug' => 'services.view', 'group' => 'services'],
            ['name' => 'Manage Services', 'slug' => 'services.manage', 'group' => 'services'],
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'group' => 'inventory'],
            ['name' => 'Manage Inventory', 'slug' => 'inventory.manage', 'group' => 'inventory'],
            ['name' => 'Access POS', 'slug' => 'pos.access', 'group' => 'sales'],
            ['name' => 'View Sales', 'slug' => 'sales.view', 'group' => 'sales'],
            ['name' => 'Manage Sales', 'slug' => 'sales.manage', 'group' => 'sales'],
            ['name' => 'View Payments', 'slug' => 'payments.view', 'group' => 'payments'],
            ['name' => 'Manage Payments', 'slug' => 'payments.manage', 'group' => 'payments'],
            ['name' => 'View Staff', 'slug' => 'staff.view', 'group' => 'staff'],
            ['name' => 'Manage Staff', 'slug' => 'staff.manage', 'group' => 'staff'],
            ['name' => 'View Reports', 'slug' => 'reports.view', 'group' => 'reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $rolePermissions = [
            RoleSlug::SuperAdmin->value => Permission::pluck('id')->all(),
            RoleSlug::Manager->value => Permission::whereNotIn('slug', ['branches.delete'])->pluck('id')->all(),
            RoleSlug::Cashier->value => Permission::whereIn('slug', [
                'dashboard.view', 'customers.view', 'customers.create', 'pos.access',
                'sales.view', 'sales.manage', 'payments.view', 'payments.manage',
            ])->pluck('id')->all(),
            RoleSlug::Receptionist->value => Permission::whereIn('group', ['dashboard', 'customers', 'vehicles', 'bookings'])->pluck('id')->all(),
            RoleSlug::Detailer->value => Permission::whereIn('group', ['dashboard', 'job-cards', 'vehicles'])->pluck('id')->all(),
            RoleSlug::InventoryManager->value => Permission::whereIn('group', ['dashboard', 'inventory'])->pluck('id')->all(),
        ];

        foreach (RoleSlug::cases() as $roleSlug) {
            $role = Role::updateOrCreate(
                ['slug' => $roleSlug->value],
                ['name' => $roleSlug->label(), 'description' => $roleSlug->label().' role']
            );

            $role->permissions()->sync($rolePermissions[$roleSlug->value] ?? []);
        }
    }
}
