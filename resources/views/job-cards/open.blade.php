<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Open Job Cards</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('job-cards.create'), 'createLabel' => 'New Job Card', 'title' => 'Open Job Cards'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Vehicle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($jobCards as $jobCard)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">#{{ $jobCard->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $jobCard->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $jobCard->vehicle?->registration_number ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $jobCard->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('job-cards.show', $jobCard) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('job-cards.edit', $jobCard) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($jobCards->isEmpty())<x-ui.empty-state title="No open job cards" description="Open job cards will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $jobCards])
    </x-ui.card>
</x-layouts.app>
