<?php

namespace App\Support;

use App\Models\Setting;

class EmailSettings
{
    public static function enabled(): bool
    {
        return filter_var(
            Setting::getValue('email', 'notifications_enabled', true),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function enable(): void
    {
        Setting::setValue('email', 'notifications_enabled', true, null, 'boolean');
    }

    public static function disable(): void
    {
        Setting::setValue('email', 'notifications_enabled', false, null, 'boolean');
    }
}
