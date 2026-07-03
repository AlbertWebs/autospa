<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Purchase Orders</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('purchase-orders.create'), 'createLabel' => 'New Purchase Order', 'title' => 'Purchase Orders'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($purchaseOrders as $purchaseOrder)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $purchaseOrder->reference ?? '#'.$purchaseOrder->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $purchaseOrder->supplier?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst($purchaseOrder->status) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($purchaseOrders->isEmpty())<x-ui.empty-state title="No purchase orders" description="Create purchase orders to restock inventory." />@endif
        @include('partials.crud.pagination', ['paginator' => $purchaseOrders])
    </x-ui.card>
</x-layouts.app>
