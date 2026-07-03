<?php

namespace App\Enums;

enum BookingType: string
{
    case Appointment = 'appointment';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::Appointment => 'Appointment',
            self::WalkIn => 'Walk-in',
        };
    }
}
