<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRoleRequest;
use App\Enums\RoleSlug;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.roles.index', [
            'roles' => Role::query()->system()->ordered()->withCount(['users', 'permissions'])->get(),
        ]);
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', Setting::class);
        abort_unless(in_array($role->slug, RoleSlug::values(), true), 404);

        return view('settings.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::query()->orderBy('group')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_unless(in_array($role->slug, RoleSlug::values(), true), 404);

        $role->update($request->safe()->except('permissions'));
        $role->permissions()->sync($request->validated('permissions', []));

        return redirect()->route('settings.roles.index')
            ->with('success', 'Role updated.');
    }
}
