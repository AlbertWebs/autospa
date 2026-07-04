<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('settings.users.index', [
            'users' => User::query()->with(['branch', 'roles'])->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('settings.users.create', [
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'roles' => Role::query()
                ->withCount('permissions')
                ->with('permissions:id,name,slug,group')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->except('roles', 'password'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $user->roles()->sync($request->validated('roles', []));

        return redirect()->route('settings.users.index')
            ->with('success', 'User created.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        return view('settings.users.show', [
            'user' => $user->load(['branch', 'roles.permissions']),
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('settings.users.edit', [
            'user' => $user->load('roles.permissions'),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'roles' => Role::query()
                ->withCount('permissions')
                ->with('permissions:id,name,slug,group')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except('roles', 'password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);
        $user->roles()->sync($request->validated('roles', []));

        return redirect()->route('settings.users.index')
            ->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'User deleted.');
    }
}
