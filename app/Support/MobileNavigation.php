<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class MobileNavigation
{
    protected array $routeMap = [
        'dashboard' => 'mobile.index',
        'job-cards.live' => 'mobile.job-cards.live',
        'job-cards.index' => 'mobile.job-cards.index',
        'job-cards.create' => 'mobile.job-cards.create',
        'bookings.index' => 'mobile.bookings.index',
        'bookings.calendar' => 'mobile.bookings.calendar',
        'bookings.walk-ins' => 'mobile.bookings.walk-ins',
        'bookings.pending' => 'mobile.bookings.index',
        'bookings.completed' => 'mobile.bookings.index',
        'bookings.cancelled' => 'mobile.bookings.index',
        'vehicles.check-in' => 'mobile.job-cards.create',
        'vehicles.active' => 'mobile.vehicles.active',
        'vehicles.ready' => 'mobile.vehicles.ready',
        'vehicles.index' => 'mobile.vehicles.index',
        'customers.index' => 'mobile.customers.index',
        'customers.loyalty' => 'mobile.customers.loyalty',
        'customers.feedback' => 'mobile.customers.feedback',
        'pos.index' => 'mobile.pos.index',
        'services.categories.index' => 'mobile.services.categories.index',
        'services.index' => 'mobile.services.index',
        'employees.index' => 'mobile.employees.index',
        'attendance.index' => 'mobile.attendance.index',
        'performance.index' => 'mobile.performance.index',
        'commissions.index' => 'mobile.commissions.index',
        'products.index' => 'mobile.products.index',
        'products.low-stock' => 'mobile.products.low-stock',
        'suppliers.index' => 'mobile.suppliers.index',
        'purchase-orders.index' => 'mobile.purchase-orders.index',
        'stock-movements.index' => 'mobile.stock-movements.index',
        'fixed-assets.index' => 'mobile.fixed-assets.index',
        'invoices.index' => 'mobile.invoices.index',
        'receipts.index' => 'mobile.receipts.index',
        'payments.index' => 'mobile.payments.index',
        'payments.cash' => 'mobile.payments.cash',
        'payments.mpesa' => 'mobile.payments.mpesa',
        'payments.card' => 'mobile.payments.card',
        'payments.bank' => 'mobile.payments.bank',
        'reports.daily' => 'mobile.reports.daily',
        'reports.weekly' => 'mobile.reports.weekly',
        'reports.monthly' => 'mobile.reports.monthly',
        'reports.revenue' => 'mobile.reports.revenue',
        'reports.profit' => 'mobile.reports.profit',
        'reports.customers' => 'mobile.reports.customers',
        'reports.staff' => 'mobile.reports.staff',
        'reports.job-cards' => 'mobile.reports.job-cards',
        'reports.inventory' => 'mobile.reports.inventory',
        'settings.company' => 'mobile.settings.company',
        'settings.branches.index' => 'mobile.settings.branches.index',
        'settings.users.index' => 'mobile.settings.users.index',
        'settings.roles.index' => 'mobile.settings.roles.index',
        'settings.integrations.index' => 'mobile.settings.integrations.index',
        'manual.index' => 'manual.index',
        'notifications.index' => 'notifications.index',
    ];

    public function tabs(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        $tabs = [
            ['label' => 'Home', 'icon' => 'home', 'route' => 'mobile.index', 'permission' => 'dashboard.view', 'pattern' => 'mobile.index'],
            ['label' => 'Live', 'icon' => 'auto_awesome', 'route' => 'mobile.job-cards.live', 'permission' => 'job-cards.view', 'pattern' => 'mobile.job-cards.*'],
            ['label' => 'Bookings', 'icon' => 'calendar_month', 'route' => 'mobile.bookings.index', 'permission' => 'bookings.view', 'pattern' => 'mobile.bookings.*'],
            ['label' => 'POS', 'icon' => 'point_of_sale', 'route' => 'mobile.pos.index', 'permission' => 'pos.access', 'feature' => 'pos', 'pattern' => 'mobile.pos.*'],
            ['label' => 'More', 'icon' => 'grid_view', 'route' => 'mobile.menu', 'permission' => null, 'pattern' => 'mobile.menu|mobile.settings.*|mobile.reports.*|mobile.invoices.*|mobile.products.*|mobile.fixed-assets.*|mobile.employees.*|mobile.services.*|mobile.suppliers.*|mobile.payments.*|mobile.receipts.*|mobile.commissions.*|mobile.attendance.*|mobile.performance.*|mobile.purchase-orders.*|mobile.stock-movements.*|mobile.customers.loyalty|mobile.customers.feedback'],
        ];

        return array_values(array_filter($tabs, function (array $tab) use ($user) {
            if (! PosSettings::navigationVisible($tab['feature'] ?? null)) {
                return false;
            }

            if ($tab['permission'] === null) {
                return $user !== null;
            }

            return $user?->hasAnyPermission([$tab['permission']]) ?? false;
        }));
    }

    public function sections(?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $sections = [];
        $currentSection = null;

        foreach (config('navigation') as $item) {
            if (isset($item['section'])) {
                $currentSection = $item['section'];

                continue;
            }

            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (! $this->hasAccess($user, $child['permission'] ?? null)) {
                        continue;
                    }

                    if (! AttendanceSettings::navigationVisible($child['feature'] ?? null)) {
                        continue;
                    }

                    if (! PosSettings::navigationVisible($child['feature'] ?? null)) {
                        continue;
                    }

                    if ($child['route'] === 'pos.index' && ($item['minimalist_only'] ?? false)) {
                        continue;
                    }

                    $sections[$currentSection ?? 'Other'][] = $this->formatMenuItem($child);
                }

                continue;
            }

            if (! $this->hasAccess($user, $item['permission'] ?? null)) {
                continue;
            }

            if (! PosSettings::navigationVisible($item['feature'] ?? null)) {
                continue;
            }

            $sections[$currentSection ?? 'Other'][] = $this->formatMenuItem($item);
        }

        return array_filter($sections, fn (array $items) => $items !== []);
    }

    public function resolveRoute(string $desktopRoute): string
    {
        $mobileRoute = $this->routeMap[$desktopRoute] ?? null;

        if ($mobileRoute && Route::has($mobileRoute)) {
            return $mobileRoute;
        }

        return $desktopRoute;
    }

    public function urlFor(string $desktopRoute, array $parameters = []): string
    {
        return route($this->resolveRoute($desktopRoute), $parameters);
    }

    protected function formatMenuItem(array $item): array
    {
        $route = $item['route'] ?? null;
        $mobileRoute = $route ? $this->resolveRoute($route) : null;

        return [
            'label' => $item['label'],
            'icon' => $this->iconFor($item),
            'route' => $mobileRoute,
            'url' => $mobileRoute ? route($mobileRoute) : '#',
        ];
    }

    protected function iconFor(array $item): string
    {
        $map = [
            'home' => 'home',
            'sparkles' => 'auto_awesome',
            'calendar' => 'calendar_month',
            'truck' => 'directions_car',
            'users' => 'group',
            'clipboard' => 'assignment',
            'shopping-cart' => 'shopping_cart',
            'user-group' => 'groups',
            'cube' => 'inventory_2',
            'credit-card' => 'payments',
            'chart-bar' => 'bar_chart',
            'book' => 'menu_book',
            'bell' => 'notifications',
            'cog' => 'settings',
        ];

        $icon = $item['icon'] ?? 'chevron_right';

        return $map[$icon] ?? 'chevron_right';
    }

    protected function hasAccess(?User $user, array|string|null $permissions): bool
    {
        if ($permissions === null) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->hasAnyPermission((array) $permissions);
    }
}
