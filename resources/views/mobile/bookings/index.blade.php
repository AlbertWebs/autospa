<x-layouts.mobile title="Bookings">
    <x-mobile.page-header title="Bookings" subtitle="Scheduled and walk-in appointments" />

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="date" name="date" value="{{ $filters['date'] }}" class="asp-input flex-1 min-w-[8rem]" onchange="this.form.submit()">
        <select name="status" class="asp-select flex-1 min-w-[8rem]" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </form>

    <div class="mb-3 flex gap-2 overflow-x-auto">
        <a href="{{ route('mobile.bookings.calendar') }}" class="asp-mobile-chip shrink-0">Calendar</a>
        <a href="{{ route('mobile.bookings.walk-ins') }}" class="asp-mobile-chip shrink-0">Walk-ins</a>
    </div>

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
            <x-ui.empty-state title="No bookings" description="Try adjusting your filters." />
        @endforelse
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</x-layouts.mobile>
