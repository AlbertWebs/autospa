<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Vehicle History</h1></x-slot>
    <x-ui.card class="mb-6">
        <dl class="grid gap-4 sm:grid-cols-3 text-sm">
            <div><dt class="text-slate-500">Registration</dt><dd class="font-medium">{{ $vehicle->registration_number }}</dd></div>
            <div><dt class="text-slate-500">Vehicle</dt><dd>{{ $vehicle->make }} {{ $vehicle->model }}</dd></div>
            <div><dt class="text-slate-500">Customer</dt><dd>{{ $vehicle->customer?->full_name ?? 'N/A' }}</dd></div>
        </dl>
    </x-ui.card>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800"><h2 class="font-semibold">Job Cards</h2></div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($vehicle->jobCards ?? [] as $jobCard)
                    <a href="{{ route('job-cards.show', $jobCard) }}" class="block px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex justify-between"><span class="font-medium">#{{ $jobCard->id }}</span><x-ui.badge color="indigo">{{ $jobCard->status }}</x-ui.badge></div>
                        <p class="text-sm text-slate-500">{{ $jobCard->created_at?->format('M j, Y') }}</p>
                    </a>
                @empty
                    <x-ui.empty-state title="No job cards" description="No service history for this vehicle." />
                @endforelse
            </div>
        </x-ui.card>
        <x-ui.card :padding="false">
            <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800"><h2 class="font-semibold">Bookings</h2></div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($vehicle->bookings ?? [] as $booking)
                    <a href="{{ route('bookings.show', $booking) }}" class="block px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div class="flex justify-between"><span class="font-medium">{{ $booking->scheduled_at?->format('M j, Y g:i A') }}</span><x-ui.badge color="indigo">{{ $booking->status }}</x-ui.badge></div>
                    </a>
                @empty
                    <x-ui.empty-state title="No bookings" description="No bookings for this vehicle." />
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
