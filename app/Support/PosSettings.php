<?php

namespace App\Support;

use App\Models\Setting;

class PosSettings
{
    public static function enabled(): bool
    {
        return filter_var(
            Setting::getValue('pos', 'enabled', true),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function navigationVisible(?string $feature): bool
    {
        if ($feature === 'pos') {
            return self::enabled();
        }

        return true;
    }
}
