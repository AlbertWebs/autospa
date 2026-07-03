<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Taxes</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('settings.taxes.create'), 'createLabel' => 'Add Tax', 'title' => 'Taxes'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($taxes as $tax)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $tax->name }} @if($tax->is_default)<x-ui.badge color="indigo">Default</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $tax->code }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $tax->rate }}%</td>
                            <td class="whitespace-nowrap px-6 py-4">@if($tax->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('settings.taxes.show', $tax) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('settings.taxes.edit', $tax) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($taxes->isEmpty())<x-ui.empty-state title="No taxes configured" description="Add tax rates for invoicing and POS." />@endif
        @include('partials.crud.pagination', ['paginator' => $taxes])
    </x-ui.card>
</x-layouts.app>
