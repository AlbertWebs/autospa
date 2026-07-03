<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Invoices</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('invoices.create'), 'createLabel' => 'New Invoice', 'title' => 'Invoices'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $invoice->number ?? '#'.$invoice->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $invoice->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($invoice->total ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $invoice->status ?? 'pending' }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->isEmpty())<x-ui.empty-state title="No invoices yet" description="Invoices from sales will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $invoices])
    </x-ui.card>
</x-layouts.app>
