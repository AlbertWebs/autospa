<x-layouts.mobile title="Mission Control">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-primary">Live Operations</p>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mission Control</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}.
                {{ now()->format('l, M j') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @include('partials.sync-status-badge')
            <span
                x-show="$store.offline.online"
                x-cloak
                class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400"
            >
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>
                Online
            </span>
            <span
                x-show="! $store.offline.online"
                x-cloak
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-400/30 bg-slate-500/10 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:text-slate-400"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                Offline
            </span>
        </div>
    </div>

    <div class="asp-mobile-kpi-grid mb-6">
        <x-mobile.stat-tile variant="revenue" label="Today's Revenue" icon="payments"
            :value="'KES ' . number_format($stats['today_revenue'], 0)" />
        <x-mobile.stat-tile variant="bookings" label="Bookings" icon="calendar_month"
            :value="$stats['today_bookings']" />
        <x-mobile.stat-tile variant="service" label="In Service" icon="build"
            :value="$stats['vehicles_in_service']"
            :href="route('mobile.job-cards.live')" />
        <x-mobile.stat-tile variant="ready" label="Ready" icon="check_circle"
            :value="$stats['vehicles_ready']"
            :href="route('mobile.vehicles.ready')" />
        <x-mobile.stat-tile variant="payments" label="Pending" icon="account_balance_wallet"
            :value="'KES ' . number_format($stats['pending_payments'], 0)" />
        <x-mobile.stat-tile variant="stock" label="Low Stock" icon="inventory_2"
            :value="$stats['low_stock_count']"
            :href="route('mobile.products.low-stock')" />
    </div>

    <section class="mb-6">
        <h2 class="asp-mobile-section-title">Today's Operations</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('mobile.job-cards.index', ['section' => 'open']) }}" class="asp-mobile-chip">
                Open <span class="ml-1 font-bold">{{ $snapshot['job_cards_open'] }}</span>
            </a>
            <a href="{{ route('mobile.job-cards.index', ['section' => 'in_progress']) }}" class="asp-mobile-chip">
                Washing <span class="ml-1 font-bold">{{ $snapshot['job_cards_in_progress'] }}</span>
            </a>
            <a href="{{ route('mobile.job-cards.index', ['section' => 'completed']) }}" class="asp-mobile-chip">
                Done <span class="ml-1 font-bold">{{ $snapshot['job_cards_completed'] }}</span>
            </a>
            <a href="{{ route('mobile.bookings.index', ['status' => 'pending']) }}" class="asp-mobile-chip">
                Pending bookings <span class="ml-1 font-bold">{{ $snapshot['bookings_pending'] }}</span>
            </a>
            <a href="{{ route('mobile.bookings.index', ['status' => 'confirmed']) }}" class="asp-mobile-chip">
                Confirmed <span class="ml-1 font-bold">{{ $snapshot['bookings_confirmed'] }}</span>
            </a>
        </div>
    </section>

    <section class="asp-mobile-card mb-6">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="asp-mobile-section-title mb-0">Revenue</h2>
            <span class="text-xs text-slate-500">6 months</span>
        </div>
        <p class="mb-3 text-2xl font-bold text-slate-900 dark:text-white">
            KES {{ number_format(array_sum($chart['data']), 0) }}
        </p>
        <div class="relative h-40 w-full">
            <canvas id="revenueChart" data-chart='@json($chart)'></canvas>
        </div>
    </section>

    @can('permission', 'pos.access')
        <section class="mb-6">
            <h2 class="asp-mobile-section-title">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                <a href="{{ route('mobile.pos.index') }}" class="asp-mobile-menu-tile">
                    <span class="material-symbols-outlined asp-mobile-menu-tile-icon">point_of_sale</span>
                    <span class="asp-mobile-menu-tile-label">Open POS</span>
                </a>
                @can('permission', 'bookings.view')
                    <a href="{{ route('mobile.bookings.walk-ins') }}" class="asp-mobile-menu-tile">
                        <span class="material-symbols-outlined asp-mobile-menu-tile-icon">directions_walk</span>
                        <span class="asp-mobile-menu-tile-label">Walk-in</span>
                    </a>
                @endcan
                @can('permission', 'job-cards.manage')
                    <a href="{{ route('mobile.job-cards.create') }}" class="asp-mobile-menu-tile">
                        <span class="material-symbols-outlined asp-mobile-menu-tile-icon">garage</span>
                        <span class="asp-mobile-menu-tile-label">Check In</span>
                    </a>
                @endcan
                @can('permission', 'customers.create')
                    <a href="{{ route('customers.create') }}" class="asp-mobile-menu-tile">
                        <span class="material-symbols-outlined asp-mobile-menu-tile-icon">person_add</span>
                        <span class="asp-mobile-menu-tile-label">Add Customer</span>
                    </a>
                @endcan
            </div>
        </section>
    @endcan

    <div class="grid gap-6 md:grid-cols-2">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="asp-mobile-section-title mb-0">Top Performers</h2>
                <a href="{{ route('mobile.performance.index') }}" class="text-xs font-semibold text-brand-primary">View all</a>
            </div>
            <div class="asp-mobile-list">
                @forelse ($topEmployees as $index => $employee)
                    <div class="asp-mobile-card flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-primary/10 text-sm font-bold text-brand-primary">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $employee->displayName() }}</p>
                            <p class="text-xs text-slate-500">{{ $employee->completed_jobs }} jobs this month</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state title="No data yet" description="Employee performance will appear here." />
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="asp-mobile-section-title">Recent Activity</h2>
            <div class="asp-mobile-list">
                @forelse ($recentActivity as $activity)
                    <div class="asp-mobile-card">
                        <p class="text-sm text-slate-800 dark:text-slate-200">{{ $activity->description ?? $activity->event }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <x-ui.empty-state title="No activity" description="Recent actions will show here." />
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.mobile>
