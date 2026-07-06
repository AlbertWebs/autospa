<x-layouts.mobile title="Walk-ins">
    <x-mobile.page-header title="Walk-ins" subtitle="Same-day arrivals" :back="route('mobile.bookings.index')" />

    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($bookings as $booking)
            <x-mobile.list-card
                :href="route('mobile.bookings.show', $booking)"
                :title="$booking->customer?->full_name ?? 'Walk-in'"
                :subtitle="$booking->vehicle?->registration_number"
                :meta="$booking->scheduled_at?->format('M j, g:i A')"
                :status="$booking->status?->label()"
            />
        @empty
            <x-ui.empty-state title="No walk-ins" />
        @endforelse
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</x-layouts.mobile>
