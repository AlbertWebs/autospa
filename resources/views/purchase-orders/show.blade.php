<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">PO {{ $purchaseOrder->reference ?? '#'.$purchaseOrder->id }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('purchase-orders.index'),
            'editRoute' => route('purchase-orders.edit', $purchaseOrder),
            'deleteRoute' => route('purchase-orders.destroy', $purchaseOrder),
            'deleteConfirm' => 'Delete this purchase order?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Supplier</dt><dd class="font-medium">{{ $purchaseOrder->supplier?->name ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst($purchaseOrder->status) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $purchaseOrder->notes ?? 'N/A' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
