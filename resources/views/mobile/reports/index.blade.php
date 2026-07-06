<x-layouts.mobile title="Reports">
    <x-mobile.page-header title="Reports" :back="route('mobile.menu')" />
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
        @foreach ([
            ['Daily', 'mobile.reports.daily', 'today'],
            ['Weekly', 'mobile.reports.weekly', 'date_range'],
            ['Monthly', 'mobile.reports.monthly', 'calendar_month'],
            ['Revenue', 'mobile.reports.revenue', 'payments'],
            ['Customers', 'mobile.reports.customers', 'group'],
            ['Staff', 'mobile.reports.staff', 'groups'],
            ['Job Cards', 'mobile.reports.job-cards', 'assignment'],
            ['Inventory', 'mobile.reports.inventory', 'inventory_2'],
        ] as [$label, $route, $icon])
            <a href="{{ route($route) }}" class="asp-mobile-menu-tile">
                <span class="material-symbols-outlined asp-mobile-menu-tile-icon">{{ $icon }}</span>
                <span class="asp-mobile-menu-tile-label">{{ $label }}</span>
            </a>
        @endforeach
    </div>
</x-layouts.mobile>
