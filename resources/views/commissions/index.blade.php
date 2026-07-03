<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Commissions</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($commissions as $commission)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $commission->employee?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($commission->amount ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $commission->period ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $commission->status ?? 'pending' }}</x-ui.badge></td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($commissions->isEmpty())<x-ui.empty-state title="No commissions" description="Staff commission records will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $commissions])
    </x-ui.card>
</x-layouts.app>
