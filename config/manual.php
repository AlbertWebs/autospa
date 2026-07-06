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
                [
                    'heading' => 'Working offline',
                    'body' => 'AutoSpa can keep working when the internet drops. A banner appears at the top when you are offline. POS checkout, job card creation, and quick customer/vehicle creation queue changes locally and sync automatically when connectivity returns. The pending-sync badge in the header shows how many changes are waiting to upload. M-Pesa STK push is disabled offline—use cash or sync later. Install the app to your home screen (PWA) for faster load times and better offline support.',
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
                    'body' => 'The Live screen shows job cards currently moving through the wash bay. Use it to see what is queued, in progress, and nearing completion. Each card displays the customer, vehicle, and selected services.',
                ],
                [
                    'heading' => 'Job cards',
                    'body' => 'Open Jobs lists new work. In Progress shows active jobs. Completed archives finished jobs. Job cards tie customers, vehicles, and services together.',
                ],
                [
                    'heading' => 'Creating & editing job cards',
                    'body' => 'When you create or edit a job card, select one or more services from the active branch catalog. At least one service is required. Selected services appear on the live board and job card detail pages. Offline creation is supported—changes sync when you are back online.',
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
                    'heading' => 'Registration numbers',
                    'body' => 'Vehicle registration numbers are stored and displayed in uppercase (e.g. KDJ 902K) for consistency across search, job cards, and reports.',
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
                [
                    'heading' => 'Offline checkout',
                    'body' => 'Cash sales can be completed offline. The sale is saved locally and uploaded when connectivity returns. M-Pesa is not available offline. Pending receipts show a waiting-to-sync state until the server confirms the sale.',
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
                    'body' => 'Review productivity metrics and commission calculations for incentivized roles. Commission rules are configured under Settings → Company.',
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
            'summary' => 'Business intelligence and operational analytics.',
            'topics' => [
                [
                    'heading' => 'Time-based reports',
                    'body' => 'Daily, weekly, and monthly summaries show trends over time. Use the date picker on the Job Cards report to view activity for a specific day.',
                ],
                [
                    'heading' => 'Revenue report',
                    'body' => 'Filter by From and To dates to see period revenue alongside today, this week, this month, and outstanding balances. Click This Month to reset to the current month. Revenue is based on paid invoice amounts in the selected range.',
                ],
                [
                    'heading' => 'Customer report',
                    'body' => 'Analytical dashboard with date filters. Review new customers in the period, active customers, period revenue, repeat rate, visit-frequency segments, fleet size breakdown, a six-month acquisition trend, top spenders, new registrations, and at-risk customers (no visit in 60 days).',
                ],
                [
                    'heading' => 'Staff report',
                    'body' => 'Team performance for a selected date range: jobs completed, in-progress workload, assigned revenue, average jobs per staff member, completion times, and commissions (when enabled). Includes job activity breakdown, completions by position, weekly trend, a staff leaderboard, and underutilized staff with no completions in the period.',
                ],
                [
                    'heading' => 'Job cards report',
                    'body' => 'Pick a report date to see open, in progress, completed, and total job cards for that day, plus week and month summaries and a detailed list of cards with activity on the selected date.',
                ],
                [
                    'heading' => 'Inventory report',
                    'body' => 'Set stock position as-of datetime and a movements date range to audit on-hand quantities, low stock, stock value, and recent stock ins.',
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
                [
                    'heading' => 'Email notifications',
                    'body' => 'System email delivery can be enabled or disabled by administrators on the server using php artisan notifications:email enable|disable|status. When disabled, outbound emails are suppressed without changing other settings.',
                ],
            ],
        ],
    ],
];
