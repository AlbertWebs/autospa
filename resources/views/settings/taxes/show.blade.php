<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $tax->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('settings.taxes.index'),
            'editRoute' => route('settings.taxes.edit', $tax),
            'deleteRoute' => route('settings.taxes.destroy', $tax),
            'deleteConfirm' => 'Delete this tax?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Code</dt><dd class="font-medium">{{ $tax->code }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Rate</dt><dd>{{ $tax->rate }}%</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($tax->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Default</dt><dd>@if($tax->is_default)<x-ui.badge color="indigo">Yes</x-ui.badge>@else No @endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
