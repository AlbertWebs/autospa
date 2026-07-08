<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Inventory</span></x-slot>

    <x-ui.section-header eyebrow="Inventory" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('fixed-assets.index'),
            'editRoute' => route('fixed-assets.edit', $asset),
            'deleteRoute' => route('fixed-assets.destroy', $asset),
            'deleteConfirm' => 'Delete this fixed asset?',
        ])
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Asset Tag" :value="$asset->asset_tag" icon="tag" />
        <x-ui.stat-card label="Purchase Cost" :value="'KES ' . number_format($asset->purchase_cost, 0)" icon="payments" variant="revenue" />
        <x-ui.stat-card label="Category" :value="$asset->category?->label() ?? '—'" icon="category" />
        <x-ui.stat-card label="Location" :value="$asset->location ?? '—'" icon="location_on" />
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Name</dt><dd class="font-medium">{{ $asset->name }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge :color="$asset->status?->badgeColor() ?? 'slate'">{{ $asset->status?->label() }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Purchase Date</dt><dd>{{ $asset->purchase_date?->format('M j, Y') ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Supplier</dt><dd>{{ $asset->supplier?->name ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Assigned To</dt><dd>{{ $asset->assignee?->full_name ?? 'Unassigned' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Record Status</dt><dd>@if($asset->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd class="text-right">{{ $asset->description ?? 'N/A' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
