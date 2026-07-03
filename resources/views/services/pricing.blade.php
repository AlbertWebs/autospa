<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Service Pricing</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Duration</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($services as $service)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $service->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $service->category?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ number_format($service->price, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $service->duration_minutes }} min</td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($services->isEmpty())<x-ui.empty-state title="No services" description="Add services to view pricing." />@endif
    </x-ui.card>
</x-layouts.app>
