<?php

namespace App\Support;

use App\Models\Setting;

class CommissionSettings
{
    public const TRIGGER_JOB_COMPLETED = 'job_completed';

    public const TRIGGER_POS_CHECKOUT = 'pos_checkout';

    public const TRIGGER_BOTH = 'both';

    public const PAYOUT_DAILY = 'daily';

    public const PAYOUT_WEEKLY = 'weekly';

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
        $trigger = Setting::getValue('commission', 'trigger', self::TRIGGER_JOB_COMPLETED);

        return in_array($trigger, [
            self::TRIGGER_JOB_COMPLETED,
            self::TRIGGER_POS_CHECKOUT,
            self::TRIGGER_BOTH,
        ], true) ? $trigger : self::TRIGGER_POS_CHECKOUT;
    }

    public static function payoutCycle(): string
    {
        $cycle = Setting::getValue('commission', 'payout_cycle', self::PAYOUT_DAILY);

        return in_array($cycle, [self::PAYOUT_DAILY, self::PAYOUT_WEEKLY], true)
            ? $cycle
            : self::PAYOUT_DAILY;
    }

    public static function isWeeklyPayout(): bool
    {
        return self::payoutCycle() === self::PAYOUT_WEEKLY;
    }

    /** @return array{start: \Carbon\Carbon, end: \Carbon\Carbon} */
    public static function periodForDate(\Carbon\Carbon $date): array
    {
        if (self::isWeeklyPayout()) {
            return [
                'start' => $date->copy()->startOfWeek(),
                'end' => $date->copy()->endOfWeek(),
            ];
        }

        return [
            'start' => $date->copy()->startOfDay(),
            'end' => $date->copy()->endOfDay(),
        ];
    }

    public static function periodLabel(\Carbon\Carbon $start, \Carbon\Carbon $end): string
    {
        if (self::isWeeklyPayout()) {
            return $start->format('M j').' – '.$end->format('M j, Y');
        }

        return $start->format('l, M j, Y');
    }

    public static function payoutCycleLabel(?string $cycle = null): string
    {
        return match ($cycle ?? self::payoutCycle()) {
            self::PAYOUT_WEEKLY => 'Weekly',
            default => 'Daily',
        };
    }

    /** @return array<string, string> */
    public static function payoutCycleOptions(): array
    {
        return [
            self::PAYOUT_DAILY => self::payoutCycleLabel(self::PAYOUT_DAILY),
            self::PAYOUT_WEEKLY => self::payoutCycleLabel(self::PAYOUT_WEEKLY),
        ];
    }

    public static function commissionsPageTitle(): string
    {
        return self::isWeeklyPayout() ? 'Weekly Commissions' : 'Daily Commissions';
    }

    public static function commissionsPageSubtitle(): string
    {
        return self::isWeeklyPayout()
            ? 'Washers earn commission after each wash. Payouts are settled weekly (Monday–Sunday).'
            : 'Washers earn commission after each wash. Payouts are settled daily.';
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
            self::TRIGGER_JOB_COMPLETED => self::triggerLabel(self::TRIGGER_JOB_COMPLETED),
            self::TRIGGER_POS_CHECKOUT => self::triggerLabel(self::TRIGGER_POS_CHECKOUT),
            self::TRIGGER_BOTH => self::triggerLabel(self::TRIGGER_BOTH),
        ];
    }
}
