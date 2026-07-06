<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Customers</span></x-slot>

    <x-ui.section-header eyebrow="Customers" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('customers.index'),
            'editRoute' => route('customers.edit', $customer),
            'deleteRoute' => route('customers.destroy', $customer),
            'deleteConfirm' => 'Delete this customer?',
        ])
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Contact Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd class="font-medium">{{ $customer->phone }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $customer->email ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">ID Number</dt><dd>{{ $customer->id_number ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Address</dt><dd>{{ $customer->address ?? 'N/A' }}</dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Notes</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $customer->notes ?? 'No notes.' }}</p>
        </x-ui.card>
    </div>
</x-layouts.app>
