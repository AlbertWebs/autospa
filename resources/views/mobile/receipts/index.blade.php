<x-layouts.mobile title="Receipts">
    <x-mobile.page-header title="Receipts" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($receipts as $receipt)
            <x-mobile.list-card
                :href="route('receipts.show', $receipt)"
                :title="$receipt->receipt_number ?? 'Receipt #' . $receipt->id"
                :subtitle="'KES ' . number_format($receipt->amount ?? 0, 0)"
                :meta="$receipt->created_at?->format('M j, Y g:i A')"
            />
        @empty
            <x-ui.empty-state title="No receipts" />
        @endforelse
    </div>
    <div class="mt-4">{{ $receipts->links() }}</div>
</x-layouts.mobile>
