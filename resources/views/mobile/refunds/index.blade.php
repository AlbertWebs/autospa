<x-layouts.mobile title="Refunds">
    <x-mobile.page-header title="Refunds" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($refunds as $refund)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">KES {{ number_format($refund->amount, 0) }}</p>
                <p class="text-slate-500">{{ $refund->created_at?->format('M j, Y') }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No refunds" />
        @endforelse
    </div>
    <div class="mt-4">{{ $refunds->links() }}</div>
</x-layouts.mobile>
