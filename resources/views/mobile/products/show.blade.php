<x-layouts.mobile :title="$product->name">
    <x-mobile.page-header :title="$product->name" :back="route('mobile.products.index')" />
    <div class="asp-mobile-card space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">SKU</span><span>{{ $product->sku }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Price</span><span>KES {{ number_format($product->selling_price, 0) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">On hand</span><span>{{ $product->quantity_on_hand }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Supplier</span><span>{{ $product->supplier?->name ?? '—' }}</span></div>
    </div>
</x-layouts.mobile>
