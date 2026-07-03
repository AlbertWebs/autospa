<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Vehicle Check-In</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('vehicles.create'), 'createLabel' => 'Add Vehicle', 'title' => 'Vehicle Check-In'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Registration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Vehicle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($vehicles as $vehicle)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $vehicle->registration_number }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $vehicle->make }} {{ $vehicle->model }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $vehicle->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($vehicles->isEmpty())<x-ui.empty-state title="No vehicles to check in" description="Vehicles awaiting check-in will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $vehicles])
    </x-ui.card>
</x-layouts.app>
