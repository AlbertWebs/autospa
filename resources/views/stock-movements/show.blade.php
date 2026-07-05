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
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Quantity</dt><dd>{{ number_format($movement->quantity, 2) }} {{ $movement->product?->unit }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Balance After</dt><dd>{{ number_format($movement->balance_after, 2) }} {{ $movement->product?->unit }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Moved At</dt><dd>{{ $movement->moved_at?->format('M j, Y g:i A') ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Recorded At</dt><dd>{{ $movement->created_at?->format('M j, Y g:i A') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Recorded By</dt><dd>{{ $movement->user?->name ?? 'System' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $movement->notes ?? 'N/A' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
