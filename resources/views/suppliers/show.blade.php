<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h1></x-slot>

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
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Contact Person</dt><dd>{{ $supplier->contact_person ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $supplier->phone ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $supplier->email ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Address</dt><dd>{{ $supplier->address ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($supplier->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
