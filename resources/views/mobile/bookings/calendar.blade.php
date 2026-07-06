<x-layouts.mobile title="Calendar">
    <x-mobile.page-header title="Calendar" subtitle="{{ $date->format('F Y') }}" :back="route('mobile.bookings.index')" />

    <div class="mb-4 flex items-center justify-between gap-2">
        <a href="{{ route('mobile.bookings.calendar', ['date' => $date->copy()->subWeek()->toDateString()]) }}" class="asp-mobile-icon-btn">
            <span class="material-symbols-outlined">chevron_left</span>
        </a>
        <span class="text-sm font-semibold">{{ $start->format('M j') }} – {{ $end->format('M j') }}</span>
        <a href="{{ route('mobile.bookings.calendar', ['date' => $date->copy()->addWeek()->toDateString()]) }}" class="asp-mobile-icon-btn">
            <span class="material-symbols-outlined">chevron_right</span>
        </a>
    </div>

    <div class="mb-4 grid grid-cols-3 gap-2">
        <div class="asp-mobile-card text-center"><p class="text-xs text-slate-500">Today</p><p class="text-xl font-bold">{{ $stats['total'] }}</p></div>
        <div class="asp-mobile-card text-center"><p class="text-xs text-slate-500">Pending</p><p class="text-xl font-bold">{{ $stats['pending'] }}</p></div>
        <div class="asp-mobile-card text-center"><p class="text-xs text-slate-500">Confirmed</p><p class="text-xl font-bold">{{ $stats['confirmed'] }}</p></div>
    </div>

    <div class="space-y-4">
        @for ($day = $start->copy(); $day->lte($end); $day->addDay())
            @php $dayBookings = $bookings->get($day->toDateString(), collect()); @endphp
            <section>
                <h3 class="mb-2 text-sm font-bold {{ $day->isToday() ? 'text-brand-primary' : 'text-slate-700 dark:text-slate-300' }}">
                    {{ $day->format('D, M j') }}
                </h3>
                @if ($dayBookings->isEmpty())
                    <p class="text-xs text-slate-400">No bookings</p>
                @else
                    <div class="asp-mobile-list">
                        @foreach ($dayBookings as $booking)
                            <x-mobile.list-card
                                :href="route('mobile.bookings.show', $booking)"
                                :title="$booking->customer?->full_name ?? 'Walk-in'"
                                :subtitle="$booking->vehicle?->registration_number"
                                :meta="$booking->scheduled_at->format('g:i A')"
                                :status="$booking->status?->label()"
                            />
                        @endforeach
                    </div>
                @endif
            </section>
        @endfor
    </div>
</x-layouts.mobile>
