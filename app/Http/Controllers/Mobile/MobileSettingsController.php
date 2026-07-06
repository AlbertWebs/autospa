<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class MobileSettingsController extends Controller
{
    public function company(): View
    {
        return view('mobile.settings.company', [
            'company' => Company::query()->first(),
        ]);
    }

    public function branches(): View
    {
        return view('mobile.settings.branches', [
            'branches' => Branch::query()->orderBy('name')->get(),
        ]);
    }

    public function users(): View
    {
        return view('mobile.settings.users', [
            'users' => User::query()->with('roles')->latest()->paginate(20),
        ]);
    }

    public function roles(): View
    {
        return view('mobile.settings.roles', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function integrations(): View
    {
        return view('mobile.settings.integrations');
    }
}
