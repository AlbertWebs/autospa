<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Cancelled Bookings</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('bookings.create'), 'createLabel' => 'New Booking', 'title' => 'Cancelled Bookings'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Vehicle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Scheduled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $booking->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $booking->vehicle?->registration_number ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $booking->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('bookings.show', $booking) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('bookings.edit', $booking) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($bookings->isEmpty())<x-ui.empty-state title="No cancelled bookings" description="Cancelled bookings will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $bookings])
    </x-ui.card>
</x-layouts.app>
