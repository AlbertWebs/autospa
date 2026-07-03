<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">SMS Campaigns</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('marketing.sms.create'), 'createLabel' => 'New SMS Campaign', 'title' => 'SMS Campaigns'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Scheduled</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($campaigns as $campaign)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $campaign->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $campaign->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('marketing.sms.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('marketing.sms.edit', $campaign) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($campaigns->isEmpty())<x-ui.empty-state title="No SMS campaigns" description="Create SMS campaigns to reach customers." />@endif
        @include('partials.crud.pagination', ['paginator' => $campaigns])
    </x-ui.card>
</x-layouts.app>
