<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('settings.view');
    }

    public function update(User $user, ?Setting $setting = null): bool
    {
        return $user->hasPermission('settings.update');
    }
}
