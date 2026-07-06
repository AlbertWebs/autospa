<?php

namespace App\Support;

use App\Models\Setting;

class AttendanceSettings
{
    public static function enabled(): bool
    {
        return filter_var(
            Setting::getValue('attendance', 'enabled', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function navigationVisible(?string $feature): bool
    {
        if ($feature === 'attendance') {
            return self::enabled();
        }

        return true;
    }
}
