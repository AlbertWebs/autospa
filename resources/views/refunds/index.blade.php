<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Refunds</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('refunds.create'), 'createLabel' => 'New Refund', 'title' => 'Refunds'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Refund #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($refunds as $refund)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">#{{ $refund->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($refund->amount ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $refund->status ?? 'pending' }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('refunds.show', $refund) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($refunds->isEmpty())<x-ui.empty-state title="No refunds" description="Refund records will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $refunds])
    </x-ui.card>
</x-layouts.app>
