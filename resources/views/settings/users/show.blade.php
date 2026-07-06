<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Settings</span></x-slot>

    <x-ui.section-header eyebrow="Settings" />

    @php
        $effectivePermissions = $user->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->unique('id')
            ->groupBy(fn ($permission) => $permission->group ?? 'other')
            ->sortKeys();
    @endphp

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('settings.users.index'),
            'editRoute' => route('settings.users.edit', $user),
            'deleteRoute' => route('settings.users.destroy', $user),
            'editVisible' => auth()->user()->can('update', $user),
            'deleteVisible' => auth()->user()->can('delete', $user),
            'deleteConfirm' => 'Delete this user permanently?',
        ])
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Account Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $user->phone ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Branch</dt><dd>{{ $user->branch?->name ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($user->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            </dl>
            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-300">
                Non-super-admin access is limited to the assigned branch shown above.
            </div>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Roles</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($user->roles as $role)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                        <p class="font-medium text-slate-900 dark:text-white">{{ $role->name }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $role->permissions->count() }} permission{{ $role->permissions->count() === 1 ? '' : 's' }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No roles assigned.</p>
                @endforelse
            </div>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Effective Access</h2>
            @if ($effectivePermissions->isEmpty())
                <p class="text-sm text-slate-500">No effective permissions.</p>
            @else
                <div class="space-y-4">
                    @foreach ($effectivePermissions as $group => $permissions)
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ \Illuminate\Support\Str::headline($group) }}</p>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $permissions->count() }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($permissions->sortBy('name') as $permission)
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
