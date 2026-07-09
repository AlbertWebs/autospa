<?php

namespace App\Providers;

use App\Enums\ActivityEvent;
use App\Observers\ModelActivityObserver;
use App\Services\ActivityLogService;
use App\Support\EmailSettings;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
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
        $this->app->singleton(ActivityLogService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Http::globalOptions($this->httpClientOptions());

        $this->registerActivityLogging();

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

    protected function registerActivityLogging(): void
    {
        if (! config('activity_log.enabled', true)) {
            return;
        }

        $observer = $this->app->make(ModelActivityObserver::class);

        foreach (config('activity_log.observed_models', []) as $modelClass) {
            $modelPath = base_path(str_replace(['App\\', '\\'], ['app/', '/'], $modelClass).'.php');

            if (! is_file($modelPath)) {
                continue;
            }

            $modelClass::observe($observer);
        }

        $activityLog = $this->app->make(ActivityLogService::class);

        Event::listen(Login::class, function (Login $event) use ($activityLog): void {
            $user = $event->user;

            if (! $user instanceof \App\Models\User) {
                return;
            }

            $activityLog->record(
                ActivityEvent::AuthLogin->value,
                "{$user->name} signed in",
                $user,
                [
                    'guard' => $event->guard,
                ],
                $user->id,
                $user->branch_id,
            );
        });

        Event::listen(Logout::class, function (Logout $event) use ($activityLog): void {
            $user = $event->user;

            if (! $user instanceof \App\Models\User) {
                return;
            }

            $activityLog->record(
                ActivityEvent::AuthLogout->value,
                "{$user->name} signed out",
                $user,
                [
                    'guard' => $event->guard,
                ],
                $user->id,
                $user->branch_id,
            );
        });
    }
}
