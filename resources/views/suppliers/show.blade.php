<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Inventory</span></x-slot>

    <x-ui.section-header eyebrow="Inventory" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('suppliers.index'),
            'editRoute' => route('suppliers.edit', $supplier),
            'deleteRoute' => route('suppliers.destroy', $supplier),
            'deleteConfirm' => 'Delete this supplier?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Contact Person</dt><dd>{{ $supplier->contact_person ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $supplier->phone ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $supplier->email ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Address</dt><dd>{{ $supplier->address ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($supplier->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
