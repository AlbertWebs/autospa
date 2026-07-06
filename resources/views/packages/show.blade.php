<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Services</span></x-slot>

    <x-ui.section-header eyebrow="Services" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('packages.index'),
            'editRoute' => route('packages.edit', $package),
            'deleteRoute' => route('packages.destroy', $package),
            'deleteConfirm' => 'Delete this package?',
        ])
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Price</dt><dd class="font-medium">{{ number_format($package->price, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Duration</dt><dd>{{ $package->duration_minutes ?? 'N/A' }} min</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($package->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $package->description ?? 'N/A' }}</dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Included Services</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($package->services ?? [] as $service)
                    <x-ui.badge color="indigo">{{ $service->name }}</x-ui.badge>
                @empty
                    <p class="text-sm text-slate-500">No services included.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
