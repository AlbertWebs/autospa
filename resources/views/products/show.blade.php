<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Inventory</span></x-slot>

    <x-ui.section-header eyebrow="Inventory" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('products.index'),
            'editRoute' => route('products.edit', $product),
            'deleteRoute' => route('products.destroy', $product),
            'deleteConfirm' => 'Delete this product?',
        ])
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">SKU</dt><dd class="font-medium">{{ $product->sku }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Supplier</dt><dd>{{ $product->supplier?->name ?? 'N/A' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Cost / Selling</dt><dd>{{ number_format($product->cost_price, 2) }} / {{ number_format($product->selling_price, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Stock</dt><dd>{{ number_format($product->quantity_on_hand, 2) }} {{ $product->unit }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Minimum Level</dt><dd>{{ $product->minimum_level }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($product->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Recent Stock Movements</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="asp-table w-full">
                    <thead>
                        <tr>
                            <x-ui.th>Date & Time</x-ui.th>
                            <x-ui.th>Type</x-ui.th>
                            <x-ui.th>Qty</x-ui.th>
                            <x-ui.th>Balance</x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->stockMovements as $movement)
                            <tr class="asp-table-row">
                                <x-ui.td muted>
                                    <a href="{{ route('stock-movements.show', $movement) }}" class="hover:text-indigo-600">
                                        {{ $movement->moved_at?->format('M j, Y g:i A') }}
                                    </a>
                                </x-ui.td>
                                <x-ui.td><x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge></x-ui.td>
                                <x-ui.td>{{ number_format($movement->quantity, 2) }}</x-ui.td>
                                <x-ui.td>{{ number_format($movement->balance_after, 2) }}</x-ui.td>
                            </tr>
                        @empty
                            <tr>
                                <x-ui.td colspan="4" muted>No stock movements recorded yet.</x-ui.td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
