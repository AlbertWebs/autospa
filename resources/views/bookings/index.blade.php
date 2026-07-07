@php
    use App\Enums\BookingStatus;
    use App\Enums\BookingType;

    $dayQuery = ['date' => $filters['date']];
@endphp

<x-ui.index-page
    eyebrow="Operations"
    title="Bookings"
    :subtitle="'Bookings for ' . $selectedDate->format('l, F j, Y') . '.'"
    :create-route="route('bookings.create')"
    create-label="New Booking"
>
    <form method="GET" action="{{ route('bookings.index') }}" class="mb-6 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-brand-border/60 dark:bg-brand-surface-high">
        <div class="grid gap-4 md:grid-cols-4">
            <x-ui.form-field label="Date" for="booking_date" hint="Show bookings scheduled on this day.">
                <x-ui.input id="booking_date" name="date" type="date" :value="$filters['date']" />
            </x-ui.form-field>

            <x-ui.form-field label="Status" for="booking_status">
                <x-ui.select id="booking_status" name="status">
                    <option value="">All statuses</option>
                    @foreach (BookingStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.form-field>

            <x-ui.form-field label="Type" for="booking_type">
                <x-ui.select id="booking_type" name="type">
                    <option value="">All types</option>
                    @foreach (BookingType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.form-field>

            <div class="flex items-end gap-2">
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Apply Filters</button>
                <a href="{{ route('bookings.index') }}" class="asp-btn asp-btn-ghost !py-2.5">Clear</a>
            </div>
        </div>
    </form>

    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('bookings.index', array_merge($dayQuery, ['type' => BookingType::WalkIn->value])) }}" class="rounded-xl border border-slate-200/80 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-brand-primary/40 hover:text-brand-primary-dim dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-200">Walk-ins</a>
        <a href="{{ route('bookings.index', array_merge($dayQuery, ['status' => BookingStatus::Pending->value])) }}" class="rounded-xl border border-slate-200/80 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-brand-primary/40 hover:text-brand-primary-dim dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-200">Pending</a>
        <a href="{{ route('bookings.index', array_merge($dayQuery, ['status' => BookingStatus::Completed->value])) }}" class="rounded-xl border border-slate-200/80 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-brand-primary/40 hover:text-brand-primary-dim dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-200">Completed</a>
        <a href="{{ route('bookings.index', array_merge($dayQuery, ['status' => BookingStatus::Cancelled->value])) }}" class="rounded-xl border border-slate-200/80 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-brand-primary/40 hover:text-brand-primary-dim dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-200">Cancelled</a>
    </div>

    <x-ui.data-table
        :paginator="$bookings"
        :empty="$bookings->isEmpty()"
        empty-title="No bookings found"
        empty-description="No bookings are scheduled for this day. Try another date or status."
    >
        <x-slot name="header">
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Vehicle</x-ui.th>
            <x-ui.th>Type</x-ui.th>
            <x-ui.th>Scheduled</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($bookings as $booking)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $booking->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td mono>{{ $booking->vehicle?->registration_number ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ $booking->type?->label() ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ $booking->scheduled_at?->format('M j, Y g:i A') ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge :color="$booking->status?->color() ?? 'slate'">{{ $booking->status?->label() ?? 'N/A' }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('bookings.show', $booking)"
                        :edit="route('bookings.edit', $booking)"
                    >
                        @if ($booking->canMarkAsDone() && app(\App\Support\RouteAccess::class)->allows(auth()->user(), route('bookings.mark-done', $booking), 'POST'))
                            <form method="POST" action="{{ route('bookings.mark-done', $booking) }}" class="inline">
                                @csrf
                                <button type="submit" class="asp-table-action text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                    <span class="material-symbols-outlined text-base">check_circle</span>
                                    Mark done
                                </button>
                            </form>
                        @endif
                    </x-ui.table-actions>
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
