<x-layouts.mobile title="Stock Movements">
    <x-mobile.page-header title="Stock Movements" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($stockMovements as $movement)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">{{ $movement->product?->name }}</p>
                <p class="text-slate-500">{{ $movement->type }} · {{ $movement->quantity }} · {{ $movement->moved_at?->format('M j') }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No movements" />
        @endforelse
    </div>
    <div class="mt-4">{{ $stockMovements->links() }}</div>
</x-layouts.mobile>
