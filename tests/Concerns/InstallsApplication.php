<?php

namespace Tests\Concerns;

use App\Services\InstallService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Schema;

trait InstallsApplication
{
    protected bool $seedInstalledApplication = true;

    protected function installApplication(): void
    {
        if (app(InstallService::class)->isInstalled()) {
            return;
        }

        $this->seed([
            CoreSeeder::class,
            BranchSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
            SettingSeeder::class,
        ]);
    }

    protected function maybeInstallApplication(): void
    {
        if (! $this->seedInstalledApplication || ! Schema::hasTable('migrations')) {
            return;
        }

        $this->installApplication();
    }
}
