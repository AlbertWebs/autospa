<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stock Movements</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('stock-movements.create'), 'createLabel' => 'Record Movement', 'title' => 'Stock Movements'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($movements as $movement)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $movement->product?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst($movement->type) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $movement->quantity }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $movement->created_at?->format('M j, Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('stock-movements.show', $movement) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->isEmpty())<x-ui.empty-state title="No stock movements" description="Record stock in/out movements here." />@endif
        @include('partials.crud.pagination', ['paginator' => $movements])
    </x-ui.card>
</x-layouts.app>
