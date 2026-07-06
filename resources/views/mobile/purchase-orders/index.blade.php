<x-layouts.mobile title="Purchase Orders">
    <x-mobile.page-header title="Purchase Orders" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($purchaseOrders as $order)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">PO #{{ $order->id }}</p>
                <p class="text-slate-500">{{ $order->supplier?->name }} · {{ $order->created_at?->format('M j, Y') }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No purchase orders" />
        @endforelse
    </div>
    <div class="mt-4">{{ $purchaseOrders->links() }}</div>
</x-layouts.mobile>
