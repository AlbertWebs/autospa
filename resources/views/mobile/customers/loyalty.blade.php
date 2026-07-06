<x-layouts.mobile title="Loyalty">
    <x-mobile.page-header title="Loyalty" :back="route('mobile.customers.index')" />

    <div class="asp-mobile-list">
        @forelse ($transactions as $transaction)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">{{ $transaction->customer?->full_name }}</p>
                <p class="text-slate-500">{{ $transaction->points }} pts · {{ $transaction->created_at->format('M j, Y') }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No loyalty activity" />
        @endforelse
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.mobile>
