<?php

namespace App\Services;

use App\Enums\RoleSlug;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallService
{
    public function isInstalled(): bool
    {
        if (! Schema::hasTable('settings')) {
            return false;
        }

        if (filter_var(Setting::getValue('app', 'installed', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (! Schema::hasTable('companies') || ! Schema::hasTable('users') || ! Schema::hasTable('roles')) {
            return false;
        }

        return Company::query()->exists()
            && User::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('slug', RoleSlug::SuperAdmin->value))
                ->exists();
    }

    public function markInstalled(): void
    {
        Setting::setValue('app', 'installed', true, null, 'boolean');
    }

    /** @return array<string, array{label: string, passed: bool, message?: string}> */
    public function requirements(): array
    {
        $phpVersion = PHP_VERSION_ID >= 80200;

        $storageWritable = is_writable(storage_path())
            && is_writable(storage_path('framework'))
            && is_writable(storage_path('logs'));

        $cacheWritable = is_writable(base_path('bootstrap/cache'));

        $databaseConnected = $this->databaseConnected();
        $migrationsReady = Schema::hasTable('migrations');

        return [
            'php' => [
                'label' => 'PHP 8.2+',
                'passed' => $phpVersion,
                'message' => $phpVersion ? null : 'PHP '.PHP_VERSION.' detected. PHP 8.2 or newer is required.',
            ],
            'storage' => [
                'label' => 'Writable storage directories',
                'passed' => $storageWritable,
                'message' => $storageWritable ? null : 'Ensure storage/ and its subdirectories are writable.',
            ],
            'cache' => [
                'label' => 'Writable bootstrap/cache',
                'passed' => $cacheWritable,
                'message' => $cacheWritable ? null : 'Ensure bootstrap/cache is writable.',
            ],
            'database' => [
                'label' => 'Database connection',
                'passed' => $databaseConnected,
                'message' => $databaseConnected ? null : 'Check your database credentials in .env and run migrations.',
            ],
            'migrations' => [
                'label' => 'Migrations applied',
                'passed' => $migrationsReady,
                'message' => $migrationsReady ? null : 'Run php artisan migrate before continuing.',
            ],
        ];
    }

    public function requirementsMet(): bool
    {
        return collect($this->requirements())->every(fn (array $requirement) => $requirement['passed']);
    }

    protected function databaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
