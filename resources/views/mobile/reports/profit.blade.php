<x-layouts.mobile :title="$title">
    <x-mobile.page-header :title="$title" :back="route('mobile.reports.index')" />

    <form method="GET" action="{{ $filterRoute }}" class="mb-4 grid grid-cols-2 gap-3">
        <x-ui.form-field label="From" for="from">
            <x-ui.input id="from" name="from" type="date" :value="$filters['from'] ?? ''" />
        </x-ui.form-field>
        <x-ui.form-field label="To" for="to">
            <x-ui.input id="to" name="to" type="date" :value="$filters['to'] ?? ''" />
        </x-ui.form-field>
        <button type="submit" class="asp-btn asp-btn-primary col-span-2 !py-2.5">Update</button>
    </form>

    <div class="mb-4 grid grid-cols-2 gap-3">
        <x-mobile.stat-tile label="Money In" icon="south_west" :value="'KES ' . number_format($report['money_in'] ?? 0, 0)" />
        <x-mobile.stat-tile label="Money Out" icon="north_east" :value="'KES ' . number_format($report['money_out'] ?? 0, 0)" />
        <x-mobile.stat-tile label="Net Profit" icon="trending_up" :value="'KES ' . number_format($report['net_profit'] ?? 0, 0)" />
        @if ($report['commissions_enabled'] ?? false)
            <x-mobile.stat-tile label="Operating" icon="analytics" :value="'KES ' . number_format($report['operating_profit'] ?? 0, 0)" />
        @endif
    </div>

    <div class="asp-mobile-card mb-4 space-y-3 text-sm">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Money In</p>
        @forelse ($report['money_in_breakdown'] ?? [] as $row)
            <div class="flex justify-between gap-3">
                <span class="text-slate-500">{{ $row['label'] }}</span>
                <span class="font-semibold">KES {{ number_format($row['total'], 0) }}</span>
            </div>
        @empty
            <p class="text-slate-500">No payments in this period.</p>
        @endforelse
    </div>

    <div class="asp-mobile-card space-y-3 text-sm">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Money Out</p>
        @foreach ([
            ['key' => 'commissions_paid', 'label' => 'Commissions paid'],
            ['key' => 'supplier_purchases', 'label' => 'Supplier purchases'],
        ] as $item)
            @php
                $row = ($report['money_out_breakdown'] ?? collect())->firstWhere('key', $item['key']) ?? ['total' => 0];
            @endphp
            <div class="flex justify-between gap-3">
                <span class="text-slate-500">{{ $item['label'] }}</span>
                <span class="font-semibold">KES {{ number_format($row['total'] ?? 0, 0) }}</span>
            </div>
        @endforeach
    </div>
</x-layouts.mobile>
