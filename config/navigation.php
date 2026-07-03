<?php

return [
    ['section' => 'Overview'],
    [
        'label' => 'Mission Control',
        'icon' => 'home',
        'route' => 'dashboard',
    ],
    ['section' => 'Operations'],
    [
        'label' => 'Bookings',
        'icon' => 'calendar',
        'children' => [
            ['label' => 'Calendar', 'route' => 'bookings.calendar'],
            ['label' => 'Walk-ins', 'route' => 'bookings.walk-ins'],
            ['label' => 'Pending', 'route' => 'bookings.pending'],
            ['label' => 'Completed', 'route' => 'bookings.completed'],
            ['label' => 'Cancelled', 'route' => 'bookings.cancelled'],
        ],
    ],
    [
        'label' => 'Vehicles',
        'icon' => 'truck',
        'children' => [
            ['label' => 'Check In', 'route' => 'vehicles.check-in'],
            ['label' => 'Active Vehicles', 'route' => 'vehicles.active'],
            ['label' => 'Ready For Pickup', 'route' => 'vehicles.ready'],
            ['label' => 'Service History', 'route' => 'vehicles.history'],
        ],
    ],
    [
        'label' => 'Customers',
        'icon' => 'users',
        'children' => [
            ['label' => 'Customer List', 'route' => 'customers.index'],
            ['label' => 'Loyalty Program', 'route' => 'customers.loyalty'],
            ['label' => 'Customer Feedback', 'route' => 'customers.feedback'],
        ],
    ],
    [
        'label' => 'Job Cards',
        'icon' => 'clipboard',
        'children' => [
            ['label' => 'Open Jobs', 'route' => 'job-cards.open'],
            ['label' => 'In Progress', 'route' => 'job-cards.in-progress'],
            ['label' => 'Completed', 'route' => 'job-cards.completed'],
        ],
    ],
    [
        'label' => 'Services',
        'icon' => 'sparkles',
        'children' => [
            ['label' => 'Categories', 'route' => 'services.categories.index'],
            ['label' => 'Services', 'route' => 'services.index'],
            ['label' => 'Packages', 'route' => 'packages.index'],
            ['label' => 'Pricing', 'route' => 'services.pricing'],
        ],
    ],
    [
        'label' => 'Staff',
        'icon' => 'user-group',
        'children' => [
            ['label' => 'Employees', 'route' => 'employees.index'],
            ['label' => 'Attendance', 'route' => 'attendance.index'],
            ['label' => 'Performance', 'route' => 'performance.index'],
            ['label' => 'Commissions', 'route' => 'commissions.index'],
        ],
    ],
    [
        'label' => 'Inventory',
        'icon' => 'cube',
        'children' => [
            ['label' => 'Products', 'route' => 'products.index'],
            ['label' => 'Suppliers', 'route' => 'suppliers.index'],
            ['label' => 'Purchases', 'route' => 'purchase-orders.index'],
            ['label' => 'Stock Movement', 'route' => 'stock-movements.index'],
            ['label' => 'Low Stock', 'route' => 'products.low-stock'],
        ],
    ],
    ['section' => 'Business'],
    [
        'label' => 'Sales',
        'icon' => 'shopping-cart',
        'children' => [
            ['label' => 'POS', 'route' => 'pos.index'],
            ['label' => 'Invoices', 'route' => 'invoices.index'],
            ['label' => 'Receipts', 'route' => 'receipts.index'],
            ['label' => 'Refunds', 'route' => 'refunds.index'],
        ],
    ],
    [
        'label' => 'Payments',
        'icon' => 'credit-card',
        'children' => [
            ['label' => 'Cash', 'route' => 'payments.cash'],
            ['label' => 'M-Pesa', 'route' => 'payments.mpesa'],
            ['label' => 'Card', 'route' => 'payments.card'],
            ['label' => 'Bank', 'route' => 'payments.bank'],
        ],
    ],
    ['section' => 'Insights'],
    [
        'label' => 'Reports',
        'icon' => 'chart-bar',
        'children' => [
            ['label' => 'Daily', 'route' => 'reports.daily'],
            ['label' => 'Weekly', 'route' => 'reports.weekly'],
            ['label' => 'Monthly', 'route' => 'reports.monthly'],
            ['label' => 'Revenue', 'route' => 'reports.revenue'],
            ['label' => 'Customers', 'route' => 'reports.customers'],
            ['label' => 'Staff', 'route' => 'reports.staff'],
            ['label' => 'Inventory', 'route' => 'reports.inventory'],
        ],
    ],
    [
        'label' => 'Marketing',
        'icon' => 'megaphone',
        'children' => [
            ['label' => 'SMS', 'route' => 'marketing.sms.index'],
            ['label' => 'Email', 'route' => 'marketing.email.index'],
            ['label' => 'Promotions', 'route' => 'promotions.index'],
            ['label' => 'Loyalty', 'route' => 'marketing.loyalty'],
        ],
    ],
    ['section' => 'System'],
    [
        'label' => 'Notifications',
        'icon' => 'bell',
        'route' => 'notifications.index',
    ],
    [
        'label' => 'Settings',
        'icon' => 'cog',
        'children' => [
            ['label' => 'Company', 'route' => 'settings.company'],
            ['label' => 'Branches', 'route' => 'settings.branches.index'],
            ['label' => 'Users', 'route' => 'settings.users.index'],
            ['label' => 'Roles', 'route' => 'settings.roles.index'],
            ['label' => 'Taxes', 'route' => 'settings.taxes.index'],
            ['label' => 'Payment Methods', 'route' => 'settings.payment-methods.index'],
            ['label' => 'Integrations', 'route' => 'settings.integrations.index'],
        ],
    ],
];
