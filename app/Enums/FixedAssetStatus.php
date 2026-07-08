<?php

namespace App\Enums;

enum FixedAssetStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Maintenance => 'In Maintenance',
            self::Disposed => 'Disposed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Maintenance => 'amber',
            self::Disposed => 'slate',
        };
    }
}
