<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('settings.users.index'),
            'editRoute' => route('settings.users.edit', $user),
            'deleteRoute' => route('settings.users.destroy', $user),
            'deleteConfirm' => 'Delete this user permanently?',
        ])
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Account Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $user->phone ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Branch</dt><dd>{{ $user->branch?->name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($user->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Roles</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($user->roles as $role)
                    <x-ui.badge color="indigo">{{ $role->name }}</x-ui.badge>
                @empty
                    <p class="text-sm text-slate-500">No roles assigned.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
