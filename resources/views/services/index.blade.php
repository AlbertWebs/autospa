<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Services</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('services.create'), 'createLabel' => 'Add Service', 'title' => 'Services'])
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Duration</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($services as $service)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $service->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $service->category?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($service->price, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $service->duration_minutes }} min</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('services.show', $service) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('services.edit', $service) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($services->isEmpty())<x-ui.empty-state title="No services yet" description="Add services your spa offers." />@endif
        @include('partials.crud.pagination', ['paginator' => $services])
    </x-ui.card>
</x-layouts.app>
