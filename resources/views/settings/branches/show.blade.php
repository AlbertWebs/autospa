<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $branch->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('settings.branches.index'),
            'editRoute' => route('settings.branches.edit', $branch),
            'deleteRoute' => route('settings.branches.destroy', $branch),
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Code</dt><dd class="font-medium">{{ $branch->code }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Address</dt><dd>{{ $branch->address ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $branch->phone ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $branch->email ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($branch->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
