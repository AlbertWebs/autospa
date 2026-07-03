<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Active = 'active';
    case CheckedIn = 'checked_in';
    case InService = 'in_service';
    case ReadyForPickup = 'ready_for_pickup';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::CheckedIn => 'Checked In',
            self::InService => 'In Service',
            self::ReadyForPickup => 'Ready For Pickup',
            self::Inactive => 'Inactive',
        };
    }
}
