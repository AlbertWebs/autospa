<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@autospa.test')->first();

        if (! $admin) {
            return;
        }

        $samples = [
            ['Welcome to AutoSpa', 'Your account is ready. Explore Mission Control to get started.'],
            ['Booking confirmed', 'Jane Mwangi has a confirmed appointment in 2 hours.'],
            ['Low stock alert', 'Premium Wax is below minimum level (3 remaining).'],
            ['Payment received', 'KES 1,200 received via M-Pesa for invoice #INV-001.'],
        ];

        foreach ($samples as [$title, $message]) {
            $admin->notify(new SystemNotification($title, $message));
        }
    }
}
