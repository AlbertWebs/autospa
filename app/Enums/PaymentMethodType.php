<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case Cash = 'cash';
    case Mpesa = 'mpesa';
    case Card = 'card';
    case Bank = 'bank';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Mpesa => 'M-Pesa',
            self::Card => 'Card',
            self::Bank => 'Bank Transfer',
        };
    }
}
