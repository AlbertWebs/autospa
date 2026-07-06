<x-layouts.mobile title="Booking">
    <x-mobile.page-header
        :title="$booking->customer?->full_name ?? 'Booking'"
        :subtitle="$booking->scheduled_at?->format('l, M j · g:i A')"
        :back="route('mobile.bookings.index')"
    />

    <div class="space-y-4">
        <div class="asp-mobile-card space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Status</span><span class="font-semibold">{{ $booking->status?->label() }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Type</span><span>{{ $booking->type?->label() ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Vehicle</span><span>{{ $booking->vehicle?->registration_number ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Phone</span><span>{{ $booking->customer?->phone ?? '—' }}</span></div>
        </div>

        @if ($booking->services->isNotEmpty())
            <div class="asp-mobile-card">
                <h3 class="mb-2 font-semibold">Services</h3>
                <ul class="space-y-1 text-sm">
                    @foreach ($booking->services as $service)
                        <li>{{ $service->name }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('bookings.show', $booking) }}" class="asp-mobile-action-btn inline-flex w-full justify-center">Full details</a>
    </div>
</x-layouts.mobile>
