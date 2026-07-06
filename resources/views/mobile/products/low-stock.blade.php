<x-layouts.mobile title="Low Stock">
    <x-mobile.page-header title="Low Stock" :back="route('mobile.products.index')" />
    <div class="asp-mobile-list">
        @forelse ($products as $product)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">{{ $product->name }}</p>
                <p class="text-rose-600">{{ $product->quantity_on_hand }} / min {{ $product->minimum_level }}</p>
            </div>
        @empty
            <x-ui.empty-state title="All stock healthy" />
        @endforelse
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.mobile>
