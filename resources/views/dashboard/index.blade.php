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
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}.
                {{ $selectedDate->format('l, F j, Y') }}
                @if (! $selectedDate->isToday())
                    <span class="text-slate-400">(not today)</span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <label for="dashboard-date" class="sr-only">Overview date</label>
                <input
                    id="dashboard-date"
                    type="date"
                    name="date"
                    value="{{ $selectedDate->toDateString() }}"
                    max="{{ now()->toDateString() }}"
                    class="rounded-xl border border-slate-200/80 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm transition focus:border-brand-primary/50 focus:outline-none focus:ring-2 focus:ring-brand-primary/20 dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-200"
                    onchange="this.form.submit()"
                >
                @if (! $selectedDate->isToday())
                    <a href="{{ route('dashboard') }}" class="asp-btn asp-btn-secondary !px-3 !py-2 text-sm">Today</a>
                @endif
            </form>

            @include('partials.sync-status-badge')

            <span
                x-show="$store.offline.online"
                x-cloak
                class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
            >
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>
                Systems Online
            </span>
            <span
                x-show="! $store.offline.online"
                x-cloak
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-400/30 bg-slate-500/10 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                Systems Offline
            </span>

            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-4 py-2.5 text-sm font-semibold text-brand-on-primary shadow-glow-sm transition hover:shadow-glow active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                Open POS
            </a>
        </div>
    </header>

    {{-- KPI grid --}}
    @php
        $today = $selectedDate->toDateString();
        $dateLabel = $selectedDate->isToday() ? 'Today' : $selectedDate->format('M j, Y');
        $revenueLabel = $dateLabel."'s Revenue";
        $bookingsLabel = $dateLabel.' Bookings';
        $commissionsLabel = $dateLabel.' Commissions';
        $washersLabel = 'Washers '.$dateLabel;
        $netProfitHint = $dateLabel."'s revenue minus commissions earned";
    @endphp
    <div class="relative mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <x-ui.stat-card variant="revenue" :label="$revenueLabel" icon="payments"
            :value="'KES ' . number_format($stats['today_revenue'], 0)" :hint="'Collected on ' . $selectedDate->format('M j, Y')"
            :href="route('reports.daily', ['date' => $today])" />
        <x-ui.stat-card variant="bookings" :label="$bookingsLabel" icon="calendar_month"
            :value="$stats['today_bookings']" hint="Scheduled & walk-ins"
            :href="route('bookings.index', ['date' => $today])" />
        <x-ui.stat-card variant="service" label="In Service" icon="build"
            :value="$stats['vehicles_in_service']" :hint="$selectedDate->isToday() ? 'On the floor now' : 'In progress on this day'"
            :href="route('job-cards.in-progress')" />
        <x-ui.stat-card variant="ready" label="Ready for Pickup" icon="check_circle"
            :value="$stats['vehicles_ready']" :hint="'Completed on ' . $selectedDate->format('M j, Y')"
            :href="route('vehicles.ready')" />
        <x-ui.stat-card variant="payments" label="Pending Payments" icon="account_balance_wallet"
            :value="'KES ' . number_format($stats['pending_payments'], 0)"
            :hint="'KES ' . number_format($stats['pending_commissions'] ?? 0, 0) . ' commissions · KES ' . number_format($stats['pending_supplier_payments'] ?? 0, 0) . ' suppliers'"
            :href="route('commissions.index')" />
        <x-ui.stat-card variant="stock" label="Low Stock" icon="inventory_2"
            :value="$stats['low_stock_count']" :hint="$stats['low_stock_count'] > 0 ? 'Needs reorder' : 'All healthy'"
            :href="route('products.low-stock')" />
        @if ($stats['commissions_enabled'] ?? false)
            <x-ui.stat-card variant="payments" :label="$commissionsLabel" icon="savings"
                :value="'KES ' . number_format($stats['today_commissions'], 0)" hint="KES {{ number_format($stats['today_commissions_pending'], 0) }} pending payout"
                :href="route('commissions.index', ['date' => $today])" />
            <x-ui.stat-card variant="revenue" label="Net Profit" icon="trending_up"
                :value="'KES ' . number_format($stats['today_net_profit'], 0)" :hint="$netProfitHint"
                :href="route('reports.profit', ['from' => $today, 'to' => $today])" />
            <x-ui.stat-card variant="bookings" :label="$washersLabel" icon="groups"
                :value="$stats['today_washers']" hint="Staff who completed washes"
                :href="route('reports.staff', ['from' => $today, 'to' => $today])" />
        @endif
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
                <x-ui.quick-action href="{{ route('commissions.index') }}" icon="savings" :label="\App\Support\CommissionSettings::commissionsPageTitle()" description="Pay washer commissions" />
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
@endsection
