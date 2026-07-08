@php
    $washesBeforeFree = \App\Support\LoyaltySettings::washesBeforeFree();
@endphp

<x-layouts.mobile title="Loyalty">
    <x-mobile.page-header title="Loyalty" :back="route('mobile.customers.index')" />

    @if ($loyaltyEnabled ?? false)
        <p class="mb-4 text-sm text-slate-500">{{ $loyaltySummary }}</p>

        <div class="mb-4 flex flex-wrap gap-2 text-[11px]">
            <span class="rounded-full bg-emerald-100 px-2 py-1 font-medium text-emerald-800">Free next</span>
            <span class="rounded-full bg-sky-100 px-2 py-1 font-medium text-sky-800">Almost</span>
            <span class="rounded-full bg-amber-100 px-2 py-1 font-medium text-amber-800">In progress</span>
        </div>
    @else
        <p class="mb-4 text-sm text-slate-500">Loyalty program is currently disabled.</p>
    @endif

    <div class="asp-mobile-list">
        @forelse ($vehicles as $entry)
            @php
                $vehicle = $entry['vehicle'];
                $borderColor = match ($entry['color']) {
                    'emerald' => 'border-emerald-400',
                    'sky' => 'border-sky-400',
                    'amber' => 'border-amber-400',
                    default => 'border-slate-200',
                };
            @endphp
            <div class="asp-mobile-card border-l-4 {{ $borderColor }} text-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ $vehicle->registration_number }}</p>
                        <p class="text-slate-500">{{ $vehicle->customer?->full_name ?? 'Walk-in' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold">{{ $entry['wash_count'] }}</p>
                        <p class="text-xs text-slate-500">washes</p>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">{{ $entry['paid_in_cycle'] }}/{{ $washesBeforeFree }}</span>
                    <x-ui.badge :color="$entry['color']">{{ $entry['status_label'] }}</x-ui.badge>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No washed vehicles" description="Completed washes will appear here." />
        @endforelse
    </div>

    <div class="mt-4">{{ $vehicles->links() }}</div>
</x-layouts.mobile>
