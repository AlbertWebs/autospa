<?php

return [
    'sections' => [
        [
            'title' => 'Getting started',
            'icon' => 'book',
            'summary' => 'How AutoSpa is organized and who can access what.',
            'topics' => [
                [
                    'heading' => 'Signing in',
                    'body' => 'Admin and Supervisor accounts sign in at the login page. Your role determines which sidebar links and actions you can see. Inactive users cannot authenticate.',
                ],
                [
                    'heading' => 'Branches',
                    'body' => 'If you belong to multiple branches, use the branch selector in the top header to switch context. Most records are scoped to the active branch.',
                ],
                [
                    'heading' => 'Navigation modes',
                    'body' => 'Beast mode shows the full sidebar including Mission Control, admin tools, and settings. Minimalist mode focuses on daily operations: Live, Bookings, Vehicles, Customers, Job Cards, POS, and Receipts.',
                ],
                [
                    'heading' => 'Permissions',
                    'body' => 'Each module is protected by permissions assigned through roles. If you see an access denied message, contact an administrator to adjust your role.',
                ],
            ],
        ],
        [
            'title' => 'Mission Control',
            'icon' => 'home',
            'summary' => 'Dashboard overview of live business performance.',
            'topics' => [
                [
                    'heading' => 'Purpose',
                    'body' => 'Mission Control gives supervisors and admins a single view of today\'s revenue, bookings, vehicles in service, vehicles ready for pickup, and outstanding payments.',
                ],
                [
                    'heading' => 'Quick actions',
                    'body' => 'Use the Open POS shortcut to jump straight to checkout when the front desk is busy.',
                ],
                [
                    'heading' => 'When to use it',
                    'body' => 'Check Mission Control at the start of a shift and throughout the day to monitor throughput and spot bottlenecks.',
                ],
            ],
        ],
        [
            'title' => 'Live operations',
            'icon' => 'sparkles',
            'summary' => 'Real-time view of work on the floor.',
            'topics' => [
                [
                    'heading' => 'Live board',
                    'body' => 'The Live screen shows job cards currently moving through the wash bay. Use it to see what is queued, in progress, and nearing completion.',
                ],
                [
                    'heading' => 'Job cards',
                    'body' => 'Open Jobs lists new work. In Progress shows active jobs. Completed archives finished jobs. Job cards tie customers, vehicles, and services together.',
                ],
            ],
        ],
        [
            'title' => 'Bookings',
            'icon' => 'calendar',
            'summary' => 'Schedule and manage customer appointments.',
            'topics' => [
                [
                    'heading' => 'Calendar',
                    'body' => 'View bookings by date and time slot. Use this to plan capacity and avoid overbooking bays.',
                ],
                [
                    'heading' => 'Walk-ins',
                    'body' => 'Register customers who arrive without an appointment and convert them into active jobs.',
                ],
                [
                    'heading' => 'Status lists',
                    'body' => 'Pending, Completed, and Cancelled lists help reception follow up on no-shows, confirmations, and historical visits.',
                ],
            ],
        ],
        [
            'title' => 'Vehicles',
            'icon' => 'truck',
            'summary' => 'Track vehicles from check-in to pickup.',
            'topics' => [
                [
                    'heading' => 'Check in',
                    'body' => 'Record a vehicle arrival, link it to a customer, and start the service workflow.',
                ],
                [
                    'heading' => 'Active vehicles',
                    'body' => 'See every vehicle currently on the premises or being serviced.',
                ],
                [
                    'heading' => 'Ready for pickup',
                    'body' => 'When work is done, vehicles appear here so front desk staff can notify customers.',
                ],
                [
                    'heading' => 'Service history',
                    'body' => 'Look up past visits, services performed, and spending for any registered vehicle.',
                ],
            ],
        ],
        [
            'title' => 'Customers',
            'icon' => 'users',
            'summary' => 'Manage customer records and relationships.',
            'topics' => [
                [
                    'heading' => 'Customer list',
                    'body' => 'Create and edit customer profiles with contact details and linked vehicles. Customers are required for POS checkout and invoicing.',
                ],
                [
                    'heading' => 'Loyalty program',
                    'body' => 'After a set number of paid washes, the next wash is free. Admins can configure the program under Settings → Company (default: 10 washes, 11th free).',
                ],
                [
                    'heading' => 'Customer feedback',
                    'body' => 'Review feedback submissions to improve service quality.',
                ],
            ],
        ],
        [
            'title' => 'Point of Sale',
            'icon' => 'shopping-cart',
            'summary' => 'Complete sales and issue receipts.',
            'topics' => [
                [
                    'heading' => 'Catalog',
                    'body' => 'Browse services and products. Tap an item to add it to the cart. Filter by type or search by name.',
                ],
                [
                    'heading' => 'Checkout flow',
                    'body' => '1) Select or create a customer. 2) Add items to the cart. 3) Choose a payment method. 4) Complete the sale to issue a receipt.',
                ],
                [
                    'heading' => 'Cash payments',
                    'body' => 'When cash is selected, confirm you have physically received the payment before the receipt is issued.',
                ],
                [
                    'heading' => 'M-Pesa payments',
                    'body' => 'M-Pesa triggers an STK push to the customer phone. Confirm the push before completing the sale.',
                ],
                [
                    'heading' => 'Receipts',
                    'body' => 'After checkout you are redirected to the receipt page where you can print or start a new sale.',
                ],
            ],
        ],
        [
            'title' => 'Services & pricing',
            'icon' => 'sparkles',
            'summary' => 'Configure what you sell.',
            'topics' => [
                [
                    'heading' => 'Categories',
                    'body' => 'Group services (e.g. exterior wash, interior, detailing) for easier catalog navigation.',
                ],
                [
                    'heading' => 'Services',
                    'body' => 'Define individual services with price and estimated duration.',
                ],
                [
                    'heading' => 'Packages',
                    'body' => 'Bundle multiple services at a combined price.',
                ],
                [
                    'heading' => 'Pricing',
                    'body' => 'View and edit service prices from the Services list. Package prices are managed on the Packages screen.',
                ],
            ],
        ],
        [
            'title' => 'Staff',
            'icon' => 'user-group',
            'summary' => 'Workforce management.',
            'topics' => [
                [
                    'heading' => 'Employees',
                    'body' => 'Maintain staff records linked to system user accounts where applicable.',
                ],
                [
                    'heading' => 'Attendance',
                    'body' => 'Track who is on shift and attendance history.',
                ],
                [
                    'heading' => 'Performance & commissions',
                    'body' => 'Review productivity metrics and commission calculations for incentivized roles.',
                ],
            ],
        ],
        [
            'title' => 'Inventory',
            'icon' => 'cube',
            'summary' => 'Products, suppliers, and stock.',
            'topics' => [
                [
                    'heading' => 'Products',
                    'body' => 'Manage retail products sold through POS with SKU, price, and stock levels.',
                ],
                [
                    'heading' => 'Suppliers & purchases',
                    'body' => 'Record suppliers and purchase orders for restocking.',
                ],
                [
                    'heading' => 'Stock movement',
                    'body' => 'Audit ins and outs. Low Stock alerts help prevent running out of key items.',
                ],
            ],
        ],
        [
            'title' => 'Sales records',
            'icon' => 'clipboard',
            'summary' => 'Invoices, receipts, and refunds.',
            'topics' => [
                [
                    'heading' => 'Invoices',
                    'body' => 'View issued invoices with line items, totals, and payment status.',
                ],
                [
                    'heading' => 'Receipts',
                    'body' => 'Browse receipts generated from POS and other payment flows.',
                ],
                [
                    'heading' => 'Refunds',
                    'body' => 'Process and review refunded transactions when customers return services or products.',
                ],
            ],
        ],
        [
            'title' => 'Payments',
            'icon' => 'credit-card',
            'summary' => 'Payment channel reporting.',
            'topics' => [
                [
                    'heading' => 'By channel',
                    'body' => 'Cash, M-Pesa, card, and bank views help reconcile takings against physical counts and bank statements.',
                ],
            ],
        ],
        [
            'title' => 'Reports',
            'icon' => 'chart-bar',
            'summary' => 'Business intelligence.',
            'topics' => [
                [
                    'heading' => 'Time-based reports',
                    'body' => 'Daily, weekly, and monthly summaries show trends over time.',
                ],
                [
                    'heading' => 'Domain reports',
                    'body' => 'Revenue, customer, staff, and inventory reports answer specific management questions.',
                ],
            ],
        ],
        [
            'title' => 'Notifications',
            'icon' => 'bell',
            'summary' => 'In-app alerts.',
            'topics' => [
                [
                    'heading' => 'Notification center',
                    'body' => 'The bell icon in the header opens system notifications. Mark items as read to keep the inbox manageable.',
                ],
            ],
        ],
        [
            'title' => 'Settings',
            'icon' => 'cog',
            'summary' => 'System configuration.',
            'topics' => [
                [
                    'heading' => 'Company',
                    'body' => 'Set legal name, contact details, and branding used on receipts and communications.',
                ],
                [
                    'heading' => 'Branches',
                    'body' => 'Add and manage branch locations. Users can be assigned to a home branch.',
                ],
                [
                    'heading' => 'Users & roles',
                    'body' => 'Create user accounts, assign roles, and control access to each module through permissions.',
                ],
                [
                    'heading' => 'Payment methods',
                    'body' => 'Configure enabled payment methods (cash, M-Pesa, etc.) for POS checkout.',
                ],
                [
                    'heading' => 'Integrations',
                    'body' => 'Connect M-Pesa, SMS, email, and other external providers. Integration drivers can be stubbed for testing.',
                ],
            ],
        ],
    ],
];
