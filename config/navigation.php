<?php

return [
    ['section' => 'Overview'],
    [
        'label' => 'Mission Control',
        'icon' => 'home',
        'route' => 'dashboard',
        'permission' => 'dashboard.view',
    ],
    ['section' => 'Operations', 'minimalist' => true],
    [
        'label' => 'Live',
        'icon' => 'sparkles',
        'route' => 'job-cards.live',
        'permission' => 'job-cards.view',
        'minimalist' => true,
    ],
    [
        'label' => 'Bookings',
        'icon' => 'calendar',
        'minimalist' => true,
        'children' => [
            ['label' => 'Calendar', 'route' => 'bookings.calendar', 'permission' => 'bookings.view', 'minimalist' => true],
            ['label' => 'All Bookings', 'route' => 'bookings.index', 'permission' => 'bookings.view', 'minimalist' => true],
        ],
    ],
    [
        'label' => 'Vehicles',
        'icon' => 'truck',
        'minimalist' => true,
        'children' => [
            ['label' => 'Check In', 'route' => 'vehicles.check-in', 'permission' => 'vehicles.manage', 'minimalist' => true],
            ['label' => 'Active Vehicles', 'route' => 'vehicles.active', 'permission' => 'vehicles.view', 'minimalist' => true],
            ['label' => 'Ready For Pickup', 'route' => 'vehicles.ready', 'permission' => 'vehicles.view', 'minimalist' => true],
            ['label' => 'Service History', 'route' => 'vehicles.history', 'permission' => 'vehicles.view', 'minimalist' => true],
        ],
    ],
    [
        'label' => 'Customers',
        'icon' => 'users',
        'minimalist' => true,
        'children' => [
            ['label' => 'Customer List', 'route' => 'customers.index', 'permission' => 'customers.view', 'minimalist' => true],
            ['label' => 'Loyalty Program', 'route' => 'customers.loyalty', 'permission' => 'customers.view', 'minimalist' => true],
            ['label' => 'Customer Feedback', 'route' => 'customers.feedback', 'permission' => 'customers.view', 'minimalist' => true],
        ],
    ],
    [
        'label' => 'Job Cards',
        'icon' => 'clipboard',
        'route' => 'job-cards.index',
        'permission' => 'job-cards.view',
        'minimalist' => true,
    ],
    [
        'label' => 'POS',
        'icon' => 'shopping-cart',
        'route' => 'pos.index',
        'permission' => 'pos.access',
        'minimalist' => true,
        'minimalist_only' => true,
    ],
    [
        'label' => 'Services',
        'icon' => 'sparkles',
        'children' => [
            ['label' => 'Categories', 'route' => 'services.categories.index', 'permission' => 'services.view'],
            ['label' => 'Services', 'route' => 'services.index', 'permission' => 'services.view'],
            ['label' => 'Packages', 'route' => 'packages.index', 'permission' => 'services.view'],
        ],
    ],
    [
        'label' => 'Staff',
        'icon' => 'user-group',
        'children' => [
            ['label' => 'Employees', 'route' => 'employees.index', 'permission' => 'staff.view'],
            ['label' => 'Attendance', 'route' => 'attendance.index', 'permission' => 'staff.view', 'feature' => 'attendance'],
            ['label' => 'Performance', 'route' => 'performance.index', 'permission' => 'staff.view'],
            ['label' => 'Commissions', 'route' => 'commissions.index', 'permission' => 'staff.view'],
        ],
    ],
    [
        'label' => 'Inventory',
        'icon' => 'cube',
        'children' => [
            ['label' => 'Products', 'route' => 'products.index', 'permission' => 'inventory.view'],
            ['label' => 'Suppliers', 'route' => 'suppliers.index', 'permission' => 'inventory.view'],
            ['label' => 'Purchases', 'route' => 'purchase-orders.index', 'permission' => 'inventory.view'],
            ['label' => 'Stock Movement', 'route' => 'stock-movements.index', 'permission' => 'inventory.view'],
            ['label' => 'Low Stock', 'route' => 'products.low-stock', 'permission' => 'inventory.view'],
        ],
    ],
    ['section' => 'Business', 'minimalist' => true],
    [
        'label' => 'Sales',
        'icon' => 'shopping-cart',
        'children' => [
            ['label' => 'POS', 'route' => 'pos.index', 'permission' => 'pos.access'],
            ['label' => 'Invoices', 'route' => 'invoices.index', 'permission' => 'sales.view'],
            ['label' => 'Receipts', 'route' => 'receipts.index', 'permission' => 'sales.view', 'minimalist' => true],
            ['label' => 'Refunds', 'route' => 'refunds.index', 'permission' => 'sales.view'],
        ],
    ],
    [
        'label' => 'Payments',
        'icon' => 'credit-card',
        'children' => [
            ['label' => 'Cash', 'route' => 'payments.cash', 'permission' => 'payments.view'],
            ['label' => 'M-Pesa', 'route' => 'payments.mpesa', 'permission' => 'payments.view'],
            ['label' => 'Card', 'route' => 'payments.card', 'permission' => 'payments.view'],
            ['label' => 'Bank', 'route' => 'payments.bank', 'permission' => 'payments.view'],
        ],
    ],
    ['section' => 'Insights'],
    [
        'label' => 'Reports',
        'icon' => 'chart-bar',
        'children' => [
            ['label' => 'Daily', 'route' => 'reports.daily', 'permission' => 'reports.view'],
            ['label' => 'Weekly', 'route' => 'reports.weekly', 'permission' => 'reports.view'],
            ['label' => 'Monthly', 'route' => 'reports.monthly', 'permission' => 'reports.view'],
            ['label' => 'Revenue', 'route' => 'reports.revenue', 'permission' => 'reports.view'],
            ['label' => 'Customers', 'route' => 'reports.customers', 'permission' => 'reports.view'],
            ['label' => 'Staff', 'route' => 'reports.staff', 'permission' => 'reports.view'],
            ['label' => 'Job Cards', 'route' => 'reports.job-cards', 'permission' => 'reports.view'],
            ['label' => 'Inventory', 'route' => 'reports.inventory', 'permission' => 'reports.view'],
        ],
    ],
    ['section' => 'System'],
    [
        'label' => 'User Manual',
        'icon' => 'book',
        'route' => 'manual.index',
        'tour' => 'user-manual',
    ],
    [
        'label' => 'Notifications',
        'icon' => 'bell',
        'route' => 'notifications.index',
    ],
    [
        'label' => 'Settings',
        'icon' => 'cog',
        'children' => [
            ['label' => 'Company', 'route' => 'settings.company', 'permission' => 'settings.view'],
            ['label' => 'Branches', 'route' => 'settings.branches.index', 'permission' => 'branches.view'],
            ['label' => 'Users', 'route' => 'settings.users.index', 'permission' => 'users.view'],
            ['label' => 'Roles', 'route' => 'settings.roles.index', 'permission' => 'settings.view'],
            ['label' => 'Payment Methods', 'route' => 'settings.payment-methods.index', 'permission' => 'settings.view'],
            ['label' => 'Integrations', 'route' => 'settings.integrations.index', 'permission' => 'settings.view'],
        ],
    ],
];
