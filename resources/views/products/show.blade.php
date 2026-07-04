<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $product->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('products.index'),
            'editRoute' => route('products.edit', $product),
            'deleteRoute' => route('products.destroy', $product),
            'deleteConfirm' => 'Delete this product?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">SKU</dt><dd class="font-medium">{{ $product->sku }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Supplier</dt><dd>{{ $product->supplier?->name ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Cost / Selling</dt><dd>{{ number_format($product->cost_price, 2) }} / {{ number_format($product->selling_price, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Stock</dt><dd>{{ $product->quantity_on_hand }} {{ $product->unit }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Minimum Level</dt><dd>{{ $product->minimum_level }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($product->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
