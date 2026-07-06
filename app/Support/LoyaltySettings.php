<?php

namespace App\Support;

use App\Models\Setting;

class LoyaltySettings
{
    public const DEFAULT_WASHES_BEFORE_FREE = 10;

    public static function enabled(): bool
    {
        return filter_var(
            Setting::getValue('loyalty', 'enabled', true),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function washesBeforeFree(): int
    {
        $value = (int) Setting::getValue('loyalty', 'washes_before_free', self::DEFAULT_WASHES_BEFORE_FREE);

        return max(1, $value);
    }

    public static function summary(): string
    {
        $count = self::washesBeforeFree();

        return "Every {$count} paid washes earns 1 free wash (the ".($count + 1).'th wash is free).';
    }
}
