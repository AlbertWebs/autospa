<?php

namespace App\Support;

use App\Models\Setting;

class CommissionSettings
{
    public const TRIGGER_JOB_COMPLETED = 'job_completed';

    public const TRIGGER_POS_CHECKOUT = 'pos_checkout';

    public const TRIGGER_BOTH = 'both';

    public static function enabled(): bool
    {
        return filter_var(
            Setting::getValue('commission', 'enabled', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function defaultRate(): float
    {
        return (float) Setting::getValue('commission', 'default_rate', 0.30);
    }

    public static function trigger(): string
    {
        $trigger = Setting::getValue('commission', 'trigger', self::TRIGGER_POS_CHECKOUT);

        return in_array($trigger, [
            self::TRIGGER_JOB_COMPLETED,
            self::TRIGGER_POS_CHECKOUT,
            self::TRIGGER_BOTH,
        ], true) ? $trigger : self::TRIGGER_POS_CHECKOUT;
    }

    public static function triggerLabel(string $trigger): string
    {
        return match ($trigger) {
            self::TRIGGER_JOB_COMPLETED => 'When job is completed',
            self::TRIGGER_POS_CHECKOUT => 'When POS checkout is paid',
            self::TRIGGER_BOTH => 'On job completion and POS checkout',
            default => 'When POS checkout is paid',
        };
    }

    /** @return array<string, string> */
    public static function triggerOptions(): array
    {
        return [
            self::TRIGGER_POS_CHECKOUT => self::triggerLabel(self::TRIGGER_POS_CHECKOUT),
            self::TRIGGER_JOB_COMPLETED => self::triggerLabel(self::TRIGGER_JOB_COMPLETED),
            self::TRIGGER_BOTH => self::triggerLabel(self::TRIGGER_BOTH),
        ];
    }
}
