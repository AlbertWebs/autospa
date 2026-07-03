<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Low Stock</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">On Hand</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Minimum</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{ $product->name } <x-ui.badge color="red">Low</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{ $product->sku }</td>
                            <td class="whitespace-nowrap px-6 py-4 text-red-600">{ $product->quantity_on_hand }</td>
                            <td class="whitespace-nowrap px-6 py-4">{ $product->minimum_level }</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{ route('products.show', $product) }" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->isEmpty())<x-ui.empty-state title="All stocked up" description="No products below minimum level." />@endif
    </x-ui.card>
</x-layouts.app>
