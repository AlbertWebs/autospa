<?php

namespace App\Enums;

enum EmployeeType: string
{
    case Supervisor = 'supervisor';
    case Attendee = 'attendee';

    public function label(): string
    {
        return match ($this) {
            self::Supervisor => 'Supervisor',
            self::Attendee => 'Attendee',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
