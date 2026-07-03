<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\User;
use App\Policies\BranchPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Branch::class => BranchPolicy::class,
        User::class => UserPolicy::class,
        Setting::class => SettingPolicy::class,
        Customer::class => CustomerPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });
    }
}
