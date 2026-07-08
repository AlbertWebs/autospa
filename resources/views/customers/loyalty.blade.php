@php
    $washesBeforeFree = \App\Support\LoyaltySettings::washesBeforeFree();
@endphp

<x-ui.index-page
    eyebrow="Customers"
    title="Loyalty Program"
    :subtitle="$loyaltyEnabled ? $loyaltySummary : 'Loyalty program is currently disabled. Enable it under Settings → Company.'"
>
    @if ($loyaltyEnabled)
        <div class="mb-6 flex flex-wrap gap-3 text-xs">
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                Free wash next ({{ $washesBeforeFree }}/{{ $washesBeforeFree }})
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 font-medium text-sky-800 dark:border-sky-800 dark:bg-sky-950 dark:text-sky-200">
                <span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                Almost there ({{ max(1, $washesBeforeFree - 2) }}–{{ $washesBeforeFree - 1 }} washes)
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 font-medium text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                Building progress
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                New cycle after free wash
            </span>
        </div>

        <form method="GET" action="{{ route('customers.loyalty') }}" class="mb-6">
            <div class="flex max-w-md gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search registration or customer..."
                    class="asp-input w-full"
                >
                <button type="submit" class="asp-btn asp-btn-secondary">Search</button>
            </div>
        </form>
    @endif

    <x-ui.data-table
        :paginator="$vehicles"
        :empty="$vehicles->isEmpty()"
        empty-title="No washed vehicles yet"
        empty-description="Completed washes will appear here with loyalty progress."
    >
        <x-slot name="header">
            <x-ui.th>Vehicle</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Total Washes</x-ui.th>
            <x-ui.th>Progress</x-ui.th>
            <x-ui.th>Status</x-ui.th>
        </x-slot>

        @foreach ($vehicles as $entry)
            @php
                $vehicle = $entry['vehicle'];
                $rowTint = match ($entry['color']) {
                    'emerald' => 'bg-emerald-50/70 dark:bg-emerald-950/25',
                    'sky' => 'bg-sky-50/70 dark:bg-sky-950/25',
                    'amber' => 'bg-amber-50/70 dark:bg-amber-950/25',
                    default => '',
                };
                $barColor = match ($entry['color']) {
                    'emerald' => 'bg-emerald-500',
                    'sky' => 'bg-sky-500',
                    'amber' => 'bg-amber-500',
                    default => 'bg-slate-400',
                };
            @endphp
            <tr class="asp-table-row {{ $rowTint }}">
                <x-ui.td primary>
                    <div class="font-semibold">{{ $vehicle->registration_number }}</div>
                    @if ($vehicle->make || $vehicle->model)
                        <div class="text-xs text-slate-500">{{ trim("{$vehicle->make} {$vehicle->model}") }}</div>
                    @endif
                </x-ui.td>
                <x-ui.td>{{ $vehicle->customer?->full_name ?? 'Walk-in' }}</x-ui.td>
                <x-ui.td>
                    <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $entry['wash_count'] }}</span>
                </x-ui.td>
                <x-ui.td>
                    <div class="min-w-[10rem]">
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ $entry['paid_in_cycle'] }}/{{ $washesBeforeFree }}</span>
                            @if ($entry['washes_until_free'] > 0)
                                <span>{{ $entry['washes_until_free'] }} to free</span>
                            @else
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Due now</span>
                            @endif
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                            <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ $entry['progress_percent'] }}%"></div>
                        </div>
                    </div>
                </x-ui.td>
                <x-ui.td>
                    <x-ui.badge :color="$entry['color']">{{ $entry['status_label'] }}</x-ui.badge>
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
