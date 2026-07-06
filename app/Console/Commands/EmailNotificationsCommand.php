<?php

namespace App\Console\Commands;

use App\Support\EmailSettings;
use Illuminate\Console\Command;

class EmailNotificationsCommand extends Command
{
    protected $signature = 'notifications:email
                            {action? : disable, enable, or status (default: status)}';

    protected $description = 'Enable or disable outgoing email notifications (verification, password reset, etc.)';

    public function handle(): int
    {
        $action = strtolower((string) ($this->argument('action') ?? 'status'));

        return match ($action) {
            'disable', 'off', 'false', '0' => $this->disable(),
            'enable', 'on', 'true', '1' => $this->enable(),
            'status' => $this->status(),
            default => $this->invalidAction($action),
        };
    }

    protected function disable(): int
    {
        EmailSettings::disable();

        $this->info('Outgoing email notifications are now disabled.');
        $this->line('Verification emails, password resets, and other mail will not be sent until re-enabled.');

        return self::SUCCESS;
    }

    protected function enable(): int
    {
        EmailSettings::enable();

        $this->info('Outgoing email notifications are now enabled.');

        return self::SUCCESS;
    }

    protected function status(): int
    {
        if (EmailSettings::enabled()) {
            $this->info('Outgoing email notifications: enabled');
        } else {
            $this->warn('Outgoing email notifications: disabled');
        }

        return self::SUCCESS;
    }

    protected function invalidAction(string $action): int
    {
        $this->error("Unknown action [{$action}]. Use disable, enable, or status.");

        return self::INVALID;
    }
}
