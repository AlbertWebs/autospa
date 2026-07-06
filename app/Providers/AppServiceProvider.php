<?php

namespace App\Providers;

use App\Support\EmailSettings;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
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
        Http::globalOptions($this->httpClientOptions());

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

    /**
     * @return array<string, mixed>
     */
    protected function httpClientOptions(): array
    {
        if (! config('http.verify_ssl', true)) {
            return ['verify' => false];
        }

        $bundle = config('http.ca_bundle');

        if (is_string($bundle) && $bundle !== '' && is_file($bundle)) {
            return ['verify' => $bundle];
        }

        $defaultBundle = storage_path('certs/cacert.pem');

        if (is_file($defaultBundle)) {
            return ['verify' => $defaultBundle];
        }

        return ['verify' => true];
    }
}
