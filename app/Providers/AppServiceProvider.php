<?php

namespace App\Providers;

use App\Support\EmailSettings;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(MessageSending::class, function (MessageSending $event): bool {
            if (app()->bound('integration_test_bypass_email')) {
                return true;
            }

            if (! EmailSettings::enabled()) {
                return false;
            }

            return true;
        });
    }
}
