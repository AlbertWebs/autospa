@php
    use App\Enums\BookingType;

    $statusClass = 'asp-status-pill asp-status-pill--' . $booking->status->value;
    $durationMinutes = $booking->bookingServices->sum('duration_minutes');
    $totalPrice = $booking->bookingServices->sum('price');
@endphp

<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Operations</span>    </x-slot>

    <x-ui.section-header eyebrow="Operations">
            <a href="{{ route('bookings.index', ['date' => $booking->scheduled_at?->toDateString() ?? today()->toDateString()]) }}" class="asp-btn asp-btn-secondary">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Back
            </a>
            @if ($booking->canMarkAsDone() && app(\App\Support\RouteAccess::class)->allows(auth()->user(), route('bookings.mark-done', $booking), 'POST'))
                <form method="POST" action="{{ route('bookings.mark-done', $booking) }}">
                    @csrf
                    <button type="submit" class="asp-btn asp-btn-primary">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        Mark done
                    </button>
                </form>
            @endif
            <a href="{{ route('bookings.edit', $booking) }}" class="asp-btn asp-btn-primary">
                <span class="material-symbols-outlined text-lg">edit</span>
                Edit
            </a>
            @if ($booking->jobCard)
                <a href="{{ route('job-cards.show', $booking->jobCard) }}" class="asp-btn asp-btn-secondary">
                    <span class="material-symbols-outlined text-lg">garage</span>
                    View Job Card
                </a>
            @else
                <a href="{{ route('job-cards.create') }}" class="asp-btn asp-btn-secondary">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    Check In
                </a>
            @endif
            <form method="POST" action="{{ route('bookings.destroy', $booking) }}" onsubmit="return confirm('Delete this booking?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="asp-btn asp-btn-danger">
                    <span class="material-symbols-outlined text-lg">delete</span>
                    Delete
                </button>
            </form>
    </x-ui.section-header>

    {{-- Hero: schedule + status --}}
    <div class="asp-detail-hero">
        <div class="asp-detail-hero-body">
            <div>
                @if ($booking->scheduled_at)
                    <p class="asp-detail-time">{{ $booking->scheduled_at->format('g:i A') }}</p>
                    <p class="asp-detail-date">{{ $booking->scheduled_at->format('l, F j, Y') }}</p>
                    @if ($booking->ends_at)
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined mr-1 align-middle text-base">schedule</span>
                            Until {{ $booking->ends_at->format('g:i A') }}
                            @if ($durationMinutes > 0)
                                <span class="text-slate-400">· est. {{ $durationMinutes }} min</span>
                            @endif
                        </p>
                    @endif
                @else
                    <p class="asp-detail-time text-slate-400">Not scheduled</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="{{ $statusClass }}">
                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                    {{ $booking->status->label() }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-white px-3 py-1 text-xs font-medium text-slate-600 dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-300">
                    @if ($booking->type === BookingType::WalkIn)
                        <span class="material-symbols-outlined text-sm">directions_walk</span>
                    @else
                        <span class="material-symbols-outlined text-sm">event</span>
                    @endif
                    {{ $booking->type->label() }}
                </span>
                @if ($booking->is_recurring)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-brand-primary/30 bg-brand-primary/10 px-3 py-1 text-xs font-medium text-brand-primary-dim dark:text-brand-primary">
                        <span class="material-symbols-outlined text-sm">autorenew</span>
                        Recurring
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Customer & vehicle --}}
        <div class="asp-panel lg:col-span-2">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Customer & Vehicle</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">person</span>
            </div>
            <div class="asp-panel-body">
                <dl class="asp-detail-dl">
                    <div>
                        <dt class="asp-detail-dt">Customer</dt>
                        <dd class="asp-detail-dd">
                            @if ($booking->customer)
                                <a href="{{ route('customers.show', $booking->customer) }}" class="asp-detail-link">
                                    {{ $booking->customer->full_name }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Vehicle</dt>
                        <dd class="asp-detail-dd">
                            @if ($booking->vehicle)
                                <a href="{{ route('vehicles.show', $booking->vehicle) }}" class="asp-detail-link">
                                    {{ $booking->vehicle->registration_number }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                                @if ($booking->vehicle->make)
                                    <p class="mt-0.5 text-xs font-normal text-slate-400">
                                        {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}
                                        @if ($booking->vehicle->color)
                                            · {{ $booking->vehicle->color }}
                                        @endif
                                    </p>
                                @endif
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="asp-detail-dt">Job Card</dt>
                        <dd class="asp-detail-dd">
                            @if ($booking->jobCard)
                                <a href="{{ route('job-cards.show', $booking->jobCard) }}" class="asp-detail-link">
                                    #{{ $booking->jobCard->id }}
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                </a>
                            @else
                                <span class="text-slate-400">Not checked in</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Services --}}
        <div class="asp-panel lg:col-span-3">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Services</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ $booking->bookingServices->count() }}
                        {{ Str::plural('service', $booking->bookingServices->count()) }}
                        @if ($totalPrice > 0)
                            · KES {{ number_format($totalPrice, 0) }}
                        @endif
                    </p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">design_services</span>
            </div>
            <div class="asp-panel-body">
                @forelse ($booking->bookingServices as $bookingService)
                    <div @class(['asp-service-row', 'mb-2' => ! $loop->last])>
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="asp-service-row-icon">
                                <span class="material-symbols-outlined text-lg">local_car_wash</span>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900 dark:text-white">
                                    {{ $bookingService->service?->name ?? 'Service' }}
                                </p>
                                @if ($bookingService->duration_minutes)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $bookingService->duration_minutes }} min
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if ($bookingService->price)
                            <p class="shrink-0 font-mono text-sm font-semibold text-slate-900 dark:text-white">
                                KES {{ number_format($bookingService->price, 0) }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <span class="material-symbols-outlined mb-2 text-4xl text-slate-300 dark:text-slate-600">design_services</span>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No services selected</p>
                        <p class="mt-1 text-xs text-slate-400">Edit this booking to add services.</p>
                        <a href="{{ route('bookings.edit', $booking) }}" class="asp-detail-link mt-3 text-sm">
                            <span class="material-symbols-outlined text-base">edit</span>
                            Add services
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if ($booking->notes)
        <div class="asp-panel mt-6">
            <div class="asp-panel-header">
                <h2 class="asp-panel-title">Notes</h2>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">sticky_note_2</span>
            </div>
            <div class="asp-panel-body">
                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $booking->notes }}</p>
            </div>
        </div>
    @endif

    {{-- Footer meta --}}
    <footer class="asp-meta-line mt-6">
        <span>Booking #{{ $booking->id }}</span>
        @if ($booking->creator)
            <span>Booked by {{ $booking->creator->name }}</span>
        @endif
        <span>Ref {{ Str::upper(Str::limit($booking->uuid, 8, '')) }}</span>
        <a href="{{ route('bookings.calendar') }}" class="asp-detail-link">View calendar</a>
    </footer>
</x-layouts.app>
