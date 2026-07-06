<x-layouts.mobile title="Products">
    <x-mobile.page-header title="Products" :back="route('mobile.menu')" />
    <div class="mb-4"><a href="{{ route('mobile.products.low-stock') }}" class="asp-mobile-chip">Low stock</a></div>
    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($products as $product)
            <x-mobile.list-card
                :href="route('mobile.products.show', $product)"
                :title="$product->name"
                :subtitle="$product->sku"
                :meta="'Stock: ' . $product->quantity_on_hand"
            />
        @empty
            <x-ui.empty-state title="No products" />
        @endforelse
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.mobile>
