@php
    use App\Enums\BookingStatus;

    $statusPillClass = fn (BookingStatus $status): string => 'asp-cal-pill asp-cal-pill--' . $status->value;

    $bookingsJson = $bookings->map(fn ($dayBookings, $dateKey) => $dayBookings->map(fn ($b) => [
        'id' => $b->id,
        'uuid' => $b->uuid,
        'url' => route('bookings.show', $b),
        'customer' => $b->customer?->full_name ?? 'Walk-in',
        'vehicle' => $b->vehicle?->registration_number ?? 'No vehicle',
        'time' => $b->scheduled_at?->format('g:i A'),
        'status' => $b->status->label(),
        'statusValue' => $b->status->value,
        'type' => $b->type->label(),
    ])->values())->toJson();
@endphp

<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Booking Calendar</span>
    </x-slot>

    <div
        x-data="{
            selectedDate: '{{ today()->format('Y-m-d') }}',
            bookingsByDay: {{ $bookingsJson }},
            selectDay(date) { this.selectedDate = date; },
            dayBookings() { return this.bookingsByDay[this.selectedDate] ?? []; },
            formatSelected() {
                if (!this.selectedDate) return '';
                const d = new Date(this.selectedDate + 'T12:00:00');
                return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
            },
            pillClass(status) { return 'asp-cal-pill asp-cal-pill--' + status; }
        }"
        class="relative"
    >
        {{-- Page header --}}
        <header class="asp-page-header">
            <div>
                <p class="asp-page-eyebrow">Operations</p>
                <h1 class="asp-page-title">Booking Calendar</h1>
                <p class="asp-page-subtitle">Schedule overview for {{ $date->format('F Y') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bookings.walk-ins') }}" class="asp-action !py-2.5 !px-4 text-sm">
                    <span class="material-symbols-outlined asp-action-icon !h-8 !w-8 !text-base">directions_walk</span>
                    Walk-in
                </a>
                <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-4 py-2.5 text-sm font-semibold text-brand-on-primary shadow-glow-sm transition hover:bg-brand-primary-dim">
                    <span class="material-symbols-outlined text-lg">add</span>
                    New Booking
                </a>
            </div>
        </header>

        {{-- Month stats --}}
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div class="asp-stat asp-stat--revenue">
                <div class="asp-stat-icon"><span class="material-symbols-outlined text-xl">calendar_month</span></div>
                <div>
                    <p class="asp-stat-label">This Month</p>
                    <p class="asp-stat-value">{{ $stats['total'] }}</p>
                </div>
            </div>
            <div class="asp-stat asp-stat--bookings">
                <div class="asp-stat-icon"><span class="material-symbols-outlined text-xl">today</span></div>
                <div>
                    <p class="asp-stat-label">Today</p>
                    <p class="asp-stat-value">{{ $stats['today'] }}</p>
                </div>
            </div>
            <div class="asp-stat asp-stat--service">
                <div class="asp-stat-icon"><span class="material-symbols-outlined text-xl">hourglass_top</span></div>
                <div>
                    <p class="asp-stat-label">Pending</p>
                    <p class="asp-stat-value">{{ $stats['pending'] }}</p>
                </div>
            </div>
            <div class="asp-stat asp-stat--ready">
                <div class="asp-stat-icon"><span class="material-symbols-outlined text-xl">event_available</span></div>
                <div>
                    <p class="asp-stat-label">Confirmed</p>
                    <p class="asp-stat-value">{{ $stats['confirmed'] }}</p>
                </div>
            </div>
            <div class="asp-stat asp-stat--stock col-span-2 sm:col-span-1">
                <div class="asp-stat-icon"><span class="material-symbols-outlined text-xl">autorenew</span></div>
                <div>
                    <p class="asp-stat-label">In Progress</p>
                    <p class="asp-stat-value">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_320px] xl:grid-cols-[1fr_360px]">
            {{-- Calendar grid --}}
            <div class="asp-cal">
                {{-- Month navigation --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3 dark:border-brand-border/60">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('bookings.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="asp-cal-nav-btn" title="Previous month">
                            <span class="material-symbols-outlined text-lg">chevron_left</span>
                        </a>
                        <h2 class="min-w-[10rem] text-center font-display text-lg font-bold text-slate-900 dark:text-white">
                            {{ $date->format('F Y') }}
                        </h2>
                        <a href="{{ route('bookings.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="asp-cal-nav-btn" title="Next month">
                            <span class="material-symbols-outlined text-lg">chevron_right</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('bookings.calendar') }}" class="rounded-lg border border-slate-200/80 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-brand-primary/40 hover:text-brand-primary-dim dark:border-brand-border/60 dark:text-slate-300 dark:hover:text-brand-primary">
                            Today
                        </a>
                        <a href="{{ route('bookings.index') }}" class="rounded-lg border border-slate-200/80 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-brand-primary/40 hover:text-brand-primary-dim dark:border-brand-border/60 dark:text-slate-300 dark:hover:text-brand-primary">
                            List view
                        </a>
                    </div>
                </div>

                {{-- Status legend --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-b border-slate-200/80 px-4 py-2 dark:border-brand-border/60">
                    @foreach (BookingStatus::cases() as $status)
                        <span class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                            <span @class([
                                'asp-cal-legend-dot',
                                'bg-amber-400' => $status === BookingStatus::Pending,
                                'bg-sky-400' => $status === BookingStatus::Confirmed,
                                'bg-indigo-400' => $status === BookingStatus::InProgress,
                                'bg-emerald-400' => $status === BookingStatus::Completed,
                                'bg-slate-400' => $status === BookingStatus::Cancelled,
                            ])></span>
                            {{ $status->label() }}
                        </span>
                    @endforeach
                </div>

                {{-- Weekday headers --}}
                <div class="grid grid-cols-7">
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                        <div class="asp-cal-weekday">{{ $weekday }}</div>
                    @endforeach
                </div>

                {{-- Day cells --}}
                @foreach ($weeks as $week)
                    <div class="grid grid-cols-7">
                        @foreach ($week as $day)
                            @php
                                $dateKey = $day->format('Y-m-d');
                                $dayBookings = $bookings->get($dateKey, collect());
                                $isCurrentMonth = $day->month === $date->month;
                                $isToday = $day->isToday();
                            @endphp
                            <button
                                type="button"
                                @click="selectDay('{{ $dateKey }}')"
                                @class([
                                    'asp-cal-cell text-left',
                                    'asp-cal-cell--muted' => ! $isCurrentMonth,
                                    'asp-cal-cell--today' => $isToday,
                                ])
                                :class="{ 'asp-cal-cell--selected': selectedDate === '{{ $dateKey }}' }"
                            >
                                <span @class([
                                    'asp-cal-day-num',
                                    'asp-cal-day-num--today' => $isToday,
                                    'opacity-40' => ! $isCurrentMonth,
                                ])>{{ $day->day }}</span>

                                <div class="hidden sm:block">
                                    @foreach ($dayBookings->take(3) as $booking)
                                        <a
                                            href="{{ route('bookings.show', $booking) }}"
                                            @click.stop
                                            class="{{ $statusPillClass($booking->status) }}"
                                            title="{{ $booking->customer?->full_name ?? 'Walk-in' }} · {{ $booking->scheduled_at?->format('g:i A') }}"
                                        >
                                            {{ $booking->scheduled_at?->format('g:i') }} {{ Str::limit($booking->customer?->full_name ?? 'Walk-in', 12) }}
                                        </a>
                                    @endforeach
                                    @if ($dayBookings->count() > 3)
                                        <span class="asp-cal-more" @click.stop="selectDay('{{ $dateKey }}')">+{{ $dayBookings->count() - 3 }} more</span>
                                    @endif
                                </div>

                                @if ($dayBookings->isNotEmpty())
                                    <span class="absolute bottom-1.5 right-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-brand-primary/20 px-1 text-[10px] font-bold text-brand-primary-dim sm:hidden dark:text-brand-primary">
                                        {{ $dayBookings->count() }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- Day detail panel --}}
            <aside class="asp-panel flex flex-col">
                <div class="border-b border-slate-200/80 px-4 py-3 dark:border-brand-border/60">
                    <p class="font-mono text-[10px] font-semibold uppercase tracking-widest text-brand-primary">Selected Day</p>
                    <h3 class="mt-1 font-display text-lg font-bold text-slate-900 dark:text-white" x-text="formatSelected()"></h3>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                        <span x-text="dayBookings().length"></span>
                        <span x-text="dayBookings().length === 1 ? 'booking' : 'bookings'"></span>
                    </p>
                </div>

                <div class="flex-1 space-y-2 overflow-y-auto p-4" style="max-height: 28rem;">
                    <template x-if="dayBookings().length === 0">
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <span class="material-symbols-outlined mb-2 text-4xl text-slate-300 dark:text-slate-600">event_busy</span>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No bookings</p>
                            <p class="mt-1 text-xs text-slate-400">This day is open for scheduling.</p>
                            <a href="{{ route('bookings.create') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-brand-primary-dim hover:underline dark:text-brand-primary">
                                <span class="material-symbols-outlined text-base">add</span>
                                Schedule booking
                            </a>
                        </div>
                    </template>

                    <template x-for="booking in dayBookings()" :key="booking.id">
                        <a :href="booking.url" class="asp-cal-detail-item group block">
                            <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-brand-primary/10 font-mono text-[10px] font-bold leading-tight text-brand-primary-dim dark:text-brand-primary">
                                <span x-text="booking.time.split(' ')[0]"></span>
                                <span class="text-[8px] font-normal opacity-70" x-text="booking.time.split(' ')[1]"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate font-medium text-slate-900 group-hover:text-brand-primary-dim dark:text-white dark:group-hover:text-brand-primary" x-text="booking.customer"></p>
                                    <span class="shrink-0 rounded-md px-1.5 py-0.5 text-[10px] font-medium" :class="pillClass(booking.statusValue)" x-text="booking.status"></span>
                                </div>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400" x-text="booking.vehicle"></p>
                                <p class="mt-0.5 text-[10px] text-slate-400" x-text="booking.type"></p>
                            </div>
                        </a>
                    </template>
                </div>

                <div class="border-t border-slate-200/80 p-4 dark:border-brand-border/60">
                    <a
                        :href="'{{ route('bookings.create') }}?scheduled_at=' + encodeURIComponent(selectedDate)"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-brand-primary/40 py-2.5 text-sm font-medium text-brand-primary-dim transition hover:border-brand-primary hover:bg-brand-primary/5 dark:text-brand-primary"
                    >
                        <span class="material-symbols-outlined text-lg">add_circle</span>
                        Add for this day
                    </a>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
