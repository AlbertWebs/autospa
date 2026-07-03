@extends('layouts.app-shell')

@section('title', 'Mission Control')

@section('content')
<div class="asp-main">
    {{-- Page header --}}
    <header class="asp-page-header">
        <div>
            <p class="asp-page-eyebrow">Live Operations</p>
            <h1 class="asp-page-title">Mission Control</h1>
            <p class="asp-page-subtitle">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }} —
                {{ now()->format('l, F j, Y') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>
                Systems Online
            </span>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-4 py-2.5 text-sm font-semibold text-brand-on-primary shadow-glow-sm transition hover:shadow-glow active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                Open POS
            </a>
        </div>
    </header>

    {{-- KPI grid --}}
    <div class="relative mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <x-ui.stat-card variant="revenue" label="Today's Revenue" icon="payments"
            :value="'KES ' . number_format($stats['today_revenue'], 0)" hint="Collected today" />
        <x-ui.stat-card variant="bookings" label="Today's Bookings" icon="calendar_month"
            :value="$stats['today_bookings']" hint="Scheduled & walk-ins" />
        <x-ui.stat-card variant="service" label="In Service" icon="build"
            :value="$stats['vehicles_in_service']" hint="On the floor now" />
        <x-ui.stat-card variant="ready" label="Ready for Pickup" icon="check_circle"
            :value="$stats['vehicles_ready']" hint="Completed today" />
        <x-ui.stat-card variant="payments" label="Pending Payments" icon="account_balance_wallet"
            :value="'KES ' . number_format($stats['pending_payments'], 0)" hint="Outstanding balance" />
        <x-ui.stat-card variant="stock" label="Low Stock" icon="inventory_2"
            :value="$stats['low_stock_count']" :hint="$stats['low_stock_count'] > 0 ? 'Needs reorder' : 'All healthy'" />
    </div>

    {{-- Chart + Quick actions --}}
    <div class="relative mb-8 grid gap-6 lg:grid-cols-3">
        <x-ui.panel title="Revenue Overview" class="lg:col-span-2" no-padding>
            <x-slot name="action">
                <span class="rounded-full bg-brand-primary/10 px-2.5 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-brand-primary-dim dark:text-brand-primary">Last 6 months</span>
            </x-slot>
            <div class="px-6 pb-6 pt-2">
                <div class="mb-4 flex items-baseline gap-2">
                    <span class="font-display text-3xl font-bold text-slate-900 dark:text-white">
                        KES {{ number_format(array_sum($chart['data']), 0) }}
                    </span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">total period</span>
                </div>
                <div class="relative h-56 w-full">
                    <canvas id="revenueChart" data-chart='@json($chart)'></canvas>
                </div>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Quick Actions">
            <div class="grid gap-2">
                <x-ui.quick-action href="{{ route('pos.index') }}" icon="point_of_sale" label="Open POS" description="Process a new sale" />
                <x-ui.quick-action href="{{ route('bookings.walk-ins') }}" icon="directions_walk" label="New Walk-in" description="Register arrival" />
                <x-ui.quick-action href="{{ route('vehicles.check-in') }}" icon="garage" label="Check In Vehicle" description="Start a job card" />
                <x-ui.quick-action href="{{ route('customers.create') }}" icon="person_add" label="Add Customer" description="New client profile" />
            </div>
        </x-ui.panel>
    </div>

    {{-- Employees + Activity --}}
    <div class="relative grid gap-6 lg:grid-cols-2">
        <x-ui.panel title="Top Performers" actionHref="{{ route('performance.index') }}" actionLabel="View all">
            <div class="space-y-1">
                @forelse ($topEmployees as $index => $employee)
                    <div class="flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-slate-50 dark:hover:bg-brand-surface/80">
                        <span class="asp-rank">{{ $index + 1 }}</span>
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-slate-200 to-slate-300 text-xs font-bold text-slate-600 dark:from-brand-surface-high dark:to-brand-border dark:text-slate-300">
                            {{ collect(explode(' ', $employee->full_name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $employee->full_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">This month</p>
                        </div>
                        <span class="rounded-lg bg-brand-primary/10 px-2.5 py-1 font-mono text-xs font-semibold text-brand-primary-dim dark:text-brand-primary">
                            {{ $employee->completed_jobs ?? 0 }} jobs
                        </span>
                    </div>
                @empty
                    <x-ui.empty-state title="No performance data yet" description="Completed jobs will rank your team here." />
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel title="Recent Activity">
            <div>
                @forelse ($recentActivity as $activity)
                    <div class="asp-timeline-item">
                        <span class="asp-timeline-dot"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ $activity->description ?? ucfirst(str_replace('_', ' ', $activity->event)) }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-500">
                                {{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state title="All quiet" description="Activity from bookings, payments, and inventory will appear here." />
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('revenueChart');
    if (!canvas || !window.Chart) return;

    const chartData = JSON.parse(canvas.dataset.chart || '{"labels":[],"data":[]}');
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(139, 144, 160, 0.15)' : 'rgba(148, 163, 184, 0.2)';
    const tickColor = isDark ? '#8b90a0' : '#64748b';

    const chart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Revenue (KES)',
                data: chartData.data,
                borderColor: '#adc6ff',
                backgroundColor: (ctx) => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                    g.addColorStop(0, isDark ? 'rgba(173, 198, 255, 0.25)' : 'rgba(79, 70, 229, 0.15)');
                    g.addColorStop(1, 'rgba(173, 198, 255, 0)');
                    return g;
                },
                fill: true,
                tension: 0.42,
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: isDark ? '#0b1326' : '#fff',
                pointBorderColor: '#adc6ff',
                pointBorderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#dae2fd' : '#0f172a',
                    bodyColor: isDark ? '#c1c6d7' : '#475569',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: (ctx) => ' KES ' + Number(ctx.raw).toLocaleString(),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: tickColor,
                        callback: (v) => 'KES ' + Number(v).toLocaleString(),
                        maxTicksLimit: 5,
                    },
                    border: { display: false },
                },
                x: {
                    grid: { display: false },
                    ticks: { color: tickColor },
                    border: { display: false },
                },
            },
        },
    });

    document.addEventListener('alpine:init', () => {
        if (!Alpine.store('theme')) return;
        Alpine.store('theme').$watch?.('dark', () => {
            const d = Alpine.store('theme').dark;
            chart.options.scales.y.grid.color = d ? 'rgba(139, 144, 160, 0.15)' : 'rgba(148, 163, 184, 0.2)';
            chart.options.scales.y.ticks.color = chart.options.scales.x.ticks.color = d ? '#8b90a0' : '#64748b';
            chart.update('none');
        });
    });
});
</script>
@endpush
@endsection
