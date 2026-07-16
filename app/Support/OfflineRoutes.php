<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class OfflineRoutes
{
    /** @var list<string> */
    protected static array $extraRouteNames = [
        'dashboard',
        'job-cards.create',
        'job-cards.live',
        'job-cards.open',
        'job-cards.in-progress',
        'job-cards.completed',
        'customers.create',
        'vehicles.create',
        'bookings.create',
        'bookings.walk-ins',
        'bookings.pending',
        'bookings.completed',
        'bookings.cancelled',
        'bookings.calendar',
        'profile.edit',
        'manual.index',
        'notifications.index',
        'mobile.index',
        'mobile.menu',
        'mobile.job-cards.index',
        'mobile.job-cards.live',
        'mobile.job-cards.create',
        'mobile.pos.index',
        'mobile.bookings.index',
        'mobile.bookings.calendar',
        'mobile.bookings.walk-ins',
        'mobile.vehicles.index',
        'mobile.vehicles.active',
        'mobile.vehicles.ready',
        'mobile.customers.index',
        'mobile.customers.loyalty',
        'mobile.customers.feedback',
        'mobile.invoices.index',
        'mobile.receipts.index',
        'mobile.payments.index',
        'mobile.payments.cash',
        'mobile.payments.mpesa',
        'mobile.payments.card',
        'mobile.payments.bank',
        'mobile.products.index',
        'mobile.products.low-stock',
        'mobile.suppliers.index',
        'mobile.purchase-orders.index',
        'mobile.stock-movements.index',
        'mobile.fixed-assets.index',
        'mobile.employees.index',
        'mobile.commissions.index',
        'mobile.performance.index',
        'mobile.attendance.index',
        'mobile.services.index',
        'mobile.services.categories.index',
        'mobile.reports.index',
        'mobile.reports.daily',
        'mobile.reports.weekly',
        'mobile.reports.monthly',
        'mobile.reports.revenue',
        'mobile.reports.profit',
        'mobile.reports.customers',
        'mobile.reports.staff',
        'mobile.reports.job-cards',
        'mobile.reports.inventory',
        'mobile.settings.company',
        'mobile.settings.branches.index',
        'mobile.settings.users.index',
        'mobile.settings.roles.index',
        'mobile.settings.integrations.index',
    ];

    /** @return list<string> */
    public static function routeNames(): array
    {
        $names = static::$extraRouteNames;

        foreach (config('navigation', []) as $item) {
            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (! empty($child['route'])) {
                        $names[] = $child['route'];
                    }
                }

                continue;
            }

            if (! empty($item['route'])) {
                $names[] = $item['route'];
            }
        }

        return array_values(array_unique($names));
    }

    /** @return list<string> */
    public static function urlsForUser(?User $user = null): array
    {
        $routeAccess = app(RouteAccess::class);
        $urls = [];

        foreach (static::routeNames() as $routeName) {
            if (! Route::has($routeName)) {
                continue;
            }

            if ($user !== null && ! $routeAccess->allows($user, $routeName)) {
                continue;
            }

            if ($routeName === 'attendance.index' && ! AttendanceSettings::enabled()) {
                continue;
            }

            if (in_array($routeName, ['pos.index', 'mobile.pos.index'], true) && ! PosSettings::enabled()) {
                continue;
            }

            $urls[] = route($routeName);
        }

        return array_values(array_unique($urls));
    }

    /** @return list<string> */
    public static function syncableMutations(): array
    {
        return [
            'customer.create',
            'vehicle.create',
            'job_card.create',
            'job_card.update_status',
            'pos.checkout',
        ];
    }

    /**
     * Routes where staff can perform offline-synced work (not just view cached pages).
     *
     * @return list<array{label: string, route: string, mobile_route?: string, icon: string, feature?: string}>
     */
    public static function operableMenuItems(): array
    {
        return [
            [
                'label' => 'Point of Sale',
                'route' => 'pos.index',
                'mobile_route' => 'mobile.pos.index',
                'icon' => 'shopping-cart',
                'feature' => 'pos',
            ],
            [
                'label' => 'Live Wash Board',
                'route' => 'job-cards.live',
                'mobile_route' => 'mobile.job-cards.live',
                'icon' => 'sparkles',
            ],
            [
                'label' => 'New Job Card',
                'route' => 'job-cards.create',
                'mobile_route' => 'mobile.job-cards.create',
                'icon' => 'clipboard',
            ],
            [
                'label' => 'Check In Vehicle',
                'route' => 'vehicles.check-in',
                'mobile_route' => 'mobile.job-cards.create',
                'icon' => 'truck',
            ],
            [
                'label' => 'Finance',
                'route' => 'finance.index',
                'icon' => 'banknotes',
            ],
        ];
    }

    /** @return list<string> */
    public static function operableRouteNames(): array
    {
        $names = [];

        foreach (static::operableMenuItems() as $item) {
            $names[] = $item['route'];

            if (! empty($item['mobile_route'])) {
                $names[] = $item['mobile_route'];
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return list<array{label: string, route: string, url: string, icon: string}>
     */
    public static function operableMenuForUser(?User $user = null, bool $mobile = false): array
    {
        $routeAccess = app(RouteAccess::class);
        $menu = [];

        foreach (static::operableMenuItems() as $item) {
            $routeName = ($mobile && ! empty($item['mobile_route']))
                ? $item['mobile_route']
                : $item['route'];

            if (! Route::has($routeName)) {
                continue;
            }

            if ($user !== null && ! $routeAccess->allows($user, $routeName)) {
                continue;
            }

            if (($item['feature'] ?? null) === 'pos' && ! PosSettings::enabled()) {
                continue;
            }

            $menu[] = [
                'label' => $item['label'],
                'route' => $routeName,
                'url' => route($routeName),
                'icon' => $item['icon'],
            ];
        }

        return $menu;
    }

    public static function isOperableRoute(?string $routeName): bool
    {
        if ($routeName === null) {
            return false;
        }

        return in_array($routeName, static::operableRouteNames(), true);
    }
}
