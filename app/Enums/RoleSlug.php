<?php

namespace App\Enums;

enum RoleSlug: string
{
    case SuperAdmin = 'super_admin';
    case Manager = 'manager';
    case Cashier = 'cashier';
    case Receptionist = 'receptionist';
    case Detailer = 'detailer';
    case InventoryManager = 'inventory_manager';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Manager => 'Manager',
            self::Cashier => 'Cashier',
            self::Receptionist => 'Receptionist',
            self::Detailer => 'Detailer',
            self::InventoryManager => 'Inventory Manager',
        };
    }
}
