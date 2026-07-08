<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RouteAccess
{
    public function allowsUrl(?User $user, ?string $url, string $method = 'GET'): bool
    {
        if (! $url) {
            return false;
        }

        $routeName = $this->routeNameFromUrl($url, $method);

        if (! $routeName) {
            return true;
        }

        return $this->allows($user, $routeName);
    }

    public function allows(?User $user, ?string $routeName): bool
    {
        if (! $routeName) {
            return false;
        }

        if (! $user) {
            return false;
        }

        $requirement = $this->requirementFor($routeName);

        if ($requirement === null) {
            return true;
        }

        $permissions = $requirement['permissions'];

        return ($requirement['mode'] ?? 'any') === 'all'
            ? $user->hasAllPermissions($permissions)
            : $user->hasAnyPermission($permissions);
    }

    protected function routeNameFromUrl(string $url, string $method): ?string
    {
        try {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $query = parse_url($url, PHP_URL_QUERY);

            if ($query) {
                $path .= '?'.$query;
            }

            return app('router')->getRoutes()->match(Request::create($path, strtoupper($method)))->getName();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function requirementFor(string $routeName): ?array
    {
        if (str_starts_with($routeName, 'mobile.')) {
            if ($routeName === 'mobile.menu') {
                return null;
            }

            return $this->requirementFor($this->desktopRouteName($routeName));
        }

        return match (true) {
            $routeName === 'dashboard' => $this->needs('dashboard.view'),
            $routeName === 'settings.company' => $this->needs('settings.view'),
            $routeName === 'settings.company.update' => $this->needs('settings.update'),
            Str::startsWith($routeName, 'settings.branches.') => $this->resourceRequirement($this->resourceAction($routeName), 'branches.view', 'branches.create', 'branches.update', 'branches.delete'),
            Str::startsWith($routeName, 'settings.users.') => $this->resourceRequirement($this->resourceAction($routeName), 'users.view', 'users.create', 'users.update', 'users.delete'),
            $routeName === 'settings.roles.index' => $this->needs('settings.view'),
            in_array($routeName, ['settings.roles.edit', 'settings.roles.update'], true) => $this->needs('settings.update'),
            $routeName === 'settings.integrations.index' => $this->needs('settings.view'),
            $routeName === 'settings.integrations.update' => $this->needs('settings.update'),
            $routeName === 'settings.business-hours.edit' => $this->needs('settings.view'),
            $routeName === 'settings.business-hours.update' => $this->needs('settings.update'),
            in_array($routeName, ['customers.loyalty', 'customers.feedback'], true) => $this->needs('customers.view'),
            Str::startsWith($routeName, 'customers.') => $this->resourceRequirement($this->resourceAction($routeName), 'customers.view', 'customers.create', 'customers.update', 'customers.delete'),
            in_array($routeName, ['vehicles.active', 'vehicles.ready', 'vehicles.history'], true) => $this->needs('vehicles.view'),
            $routeName === 'vehicles.check-in' => $this->needs('vehicles.manage'),
            Str::startsWith($routeName, 'vehicles.') => $this->resourceRequirement($this->resourceAction($routeName), 'vehicles.view', 'vehicles.manage'),
            Str::startsWith($routeName, 'bookings.') && in_array($this->resourceAction($routeName), ['calendar', 'walk-ins', 'pending', 'completed', 'cancelled'], true) => $this->needs('bookings.view'),
            $routeName === 'bookings.mark-done' => $this->needs('bookings.manage'),
            Str::startsWith($routeName, 'bookings.') => $this->resourceRequirement($this->resourceAction($routeName), 'bookings.view', 'bookings.manage'),
            Str::startsWith($routeName, 'job-cards.') && in_array($this->resourceAction($routeName), ['live', 'open', 'in-progress', 'completed'], true) => $this->needs('job-cards.view'),
            $routeName === 'job-cards.live-status' => $this->needs('job-cards.manage'),
            Str::startsWith($routeName, 'job-cards.') => $this->resourceRequirement($this->resourceAction($routeName), 'job-cards.view', 'job-cards.manage'),
            Str::startsWith($routeName, 'services.categories.') => $this->resourceRequirement($this->resourceAction($routeName), 'services.view', 'services.manage'),
            Str::startsWith($routeName, 'services.') => $this->resourceRequirement($this->resourceAction($routeName), 'services.view', 'services.manage'),
            $routeName === 'products.low-stock' => $this->needs('inventory.view'),
            Str::startsWith($routeName, 'products.') => $this->resourceRequirement($this->resourceAction($routeName), 'inventory.view', 'inventory.manage'),
            Str::startsWith($routeName, 'suppliers.') => $this->resourceRequirement($this->resourceAction($routeName), 'inventory.view', 'inventory.manage'),
            Str::startsWith($routeName, 'fixed-assets.') => $this->resourceRequirement($this->resourceAction($routeName), 'inventory.view', 'inventory.manage'),
            Str::startsWith($routeName, 'purchase-orders.') => $this->resourceRequirement($this->resourceAction($routeName), 'inventory.view', 'inventory.manage'),
            Str::startsWith($routeName, 'stock-movements.') => $this->resourceRequirement($this->resourceAction($routeName), 'inventory.view', 'inventory.manage'),
            $routeName === 'pos.index' => $this->needs('pos.access'),
            in_array($routeName, ['pos.store', 'pos.stk-push'], true) => $this->needs('pos.access'),
            Str::startsWith($routeName, 'invoices.') => $this->resourceRequirement($this->resourceAction($routeName), 'sales.view', 'sales.manage'),
            $routeName === 'receipts.index' => $this->needs('sales.view'),
            $routeName === 'receipts.show' => $this->needs('pos.access', 'sales.view'),
            in_array($routeName, ['payments.cash', 'payments.mpesa', 'payments.card', 'payments.bank'], true) => $this->needs('payments.view'),
            Str::startsWith($routeName, 'payments.') => $this->resourceRequirement($this->resourceAction($routeName), 'payments.view', 'payments.manage'),
            Str::startsWith($routeName, 'employees.') => $this->resourceRequirement($this->resourceAction($routeName), 'staff.view', 'staff.manage'),
            Str::startsWith($routeName, 'attendance.') => $this->resourceRequirement($this->resourceAction($routeName), 'staff.view', 'staff.manage'),
            $routeName === 'commissions.pay' => $this->needs('staff.manage'),
            in_array($routeName, ['commissions.pay.mpesa.initiate', 'commissions.pay.mpesa.confirm'], true) => $this->needs('staff.manage'),
            Str::startsWith($routeName, 'commissions.') => $this->needs('staff.view'),
            Str::startsWith($routeName, 'performance.') => $this->needs('staff.view'),
            Str::startsWith($routeName, 'reports.') => $this->needs('reports.view'),
            default => null,
        };
    }

    protected function resourceRequirement(
        string $action,
        string $viewPermission,
        string $createPermission,
        ?string $updatePermission = null,
        ?string $deletePermission = null,
    ): ?array {
        return match ($action) {
            'index', 'show' => $this->needs($viewPermission),
            'create', 'store' => $this->needs($createPermission),
            'edit', 'update' => $this->needs($updatePermission ?? $createPermission),
            'destroy' => $this->needs($deletePermission ?? $updatePermission ?? $createPermission),
            default => null,
        };
    }

    protected function resourceAction(string $routeName): string
    {
        return Str::afterLast($routeName, '.');
    }

    protected function needs(string ...$permissions): array
    {
        return [
            'mode' => 'any',
            'permissions' => $permissions,
        ];
    }

    protected function needsAll(string ...$permissions): array
    {
        return [
            'mode' => 'all',
            'permissions' => $permissions,
        ];
    }

    protected function desktopRouteName(string $mobileRouteName): string
    {
        return match ($mobileRouteName) {
            'mobile.index' => 'dashboard',
            'mobile.services.categories.index' => 'services.categories.index',
            'mobile.settings.branches.index' => 'settings.branches.index',
            'mobile.settings.users.index' => 'settings.users.index',
            'mobile.settings.roles.index' => 'settings.roles.index',
            'mobile.settings.integrations.index' => 'settings.integrations.index',
            'mobile.settings.company' => 'settings.company',
            'mobile.purchase-orders.index' => 'purchase-orders.index',
            'mobile.stock-movements.index' => 'stock-movements.index',
            default => substr($mobileRouteName, strlen('mobile.')),
        };
    }
}
