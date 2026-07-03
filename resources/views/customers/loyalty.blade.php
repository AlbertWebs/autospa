<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Loyalty Points</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $transaction->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $transaction->type }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $transaction->points }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $transaction->created_at?->format('M j, Y') }}</td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->isEmpty())<x-ui.empty-state title="No loyalty transactions" description="Customer loyalty activity will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $transactions])
    </x-ui.card>
</x-layouts.app>
