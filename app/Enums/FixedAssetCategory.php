<?php

namespace App\Enums;

enum FixedAssetCategory: string
{
    case Equipment = 'equipment';
    case Furniture = 'furniture';
    case Vehicle = 'vehicle';
    case It = 'it';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Equipment => 'Equipment',
            self::Furniture => 'Furniture',
            self::Vehicle => 'Vehicle',
            self::It => 'IT & Electronics',
            self::Other => 'Other',
        };
    }
}
