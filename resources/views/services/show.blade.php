<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $service->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('services.index'),
            'editRoute' => route('services.edit', $service),
            'deleteRoute' => route('services.destroy', $service),
            'deleteConfirm' => 'Delete this service?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Category</dt><dd>{{ $service->category?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Price</dt><dd class="font-medium">{{ number_format($service->price, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Duration</dt><dd>{{ $service->duration_minutes }} minutes</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($service->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $service->description ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
