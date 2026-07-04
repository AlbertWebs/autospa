<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stock Movement</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('stock-movements.index'),
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Product</dt><dd class="font-medium">{{ $movement->product?->name ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Type</dt><dd><x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Quantity</dt><dd>{{ $movement->quantity }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $movement->notes ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $movement->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
