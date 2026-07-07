<?php

return [
    'sections' => [
        [
            'title' => 'Getting started',
            'icon' => 'book',
            'summary' => 'How AutoSpa is organized, who can access what, and how to sign in.',
            'topics' => [
                [
                    'heading' => 'First-time setup',
                    'body' => 'New installations run the setup wizard: welcome, business details, first branch, admin account, optional supervisor, and preferences (commissions, loyalty, SMS). Complete setup before staff sign in.',
                ],
                [
                    'heading' => 'Signing in',
                    'body' => 'Admins and supervisors sign in with email and password. Staff with a PIN set by an administrator can use the PIN tab on the login page for quick access to the mobile cockpit. Inactive users cannot authenticate.',
                ],
                [
                    'heading' => 'Roles & permissions',
                    'body' => 'AutoSpa has two built-in roles: Admin (full system access) and Supervisor (branch operations). Assign roles under Settings → Users. Fine-tune Supervisor permissions under Settings → Roles. The sidebar only shows modules your role can access.',
                ],
                [
                    'heading' => 'Branches',
                    'body' => 'If you belong to multiple branches, use the branch selector in the top header to switch context. Most records, reports, and dashboards are scoped to the active branch.',
                ],
                [
                    'heading' => 'Navigation modes',
                    'body' => 'Beast mode shows the full sidebar: Mission Control, admin tools, inventory, reports, and settings. Minimalist mode focuses on daily operations—Live, Bookings, Vehicles, Customers, Job Cards, POS, and Receipts. Toggle modes from the header.',
                ],
                [
                    'heading' => 'Header tools',
                    'body' => 'The top bar includes branch switching, dark mode, navigation mode, fullscreen, sync status (pending offline changes), and the notification bell. Install the app to your home screen (PWA) for faster load times.',
                ],
                [
                    'heading' => 'Guided tour',
                    'body' => 'First-time users see a short onboarding tour on Mission Control. Reopen it anytime from this User Manual via Start guided tour.',
                ],
                [
                    'heading' => 'Working offline',
                    'body' => 'AutoSpa keeps working when the internet drops. A banner appears when you are offline. POS cash checkout, job card creation, and quick customer/vehicle creation queue locally and sync when connectivity returns. The pending-sync badge shows how many changes are waiting to upload. M-Pesa STK push is disabled offline—use cash or sync later.',
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
                    'body' => 'Mission Control is your daily command centre. It shows today\'s revenue, bookings, vehicles in service, vehicles ready for pickup, outstanding payments, and low-stock alerts.',
                ],
                [
                    'heading' => 'Commission stats',
                    'body' => 'When commissions are enabled, additional cards show today\'s accrued washer commissions, pending payout totals, and how many washers completed washes today.',
                ],
                [
                    'heading' => 'Revenue chart',
                    'body' => 'The six-month revenue chart helps spot trends. Use quick links to jump into Live, Bookings, or POS when the floor is busy.',
                ],
                [
                    'heading' => 'Recent activity',
                    'body' => 'The Recent Activity panel lists the latest actions in your branch—job card updates, new bookings, commission accruals, payouts, logins, and more. Use it to audit what happened during a shift without opening individual records.',
                ],
                [
                    'heading' => 'Team performance',
                    'body' => 'When staff complete washes, a leaderboard ranks washers by jobs completed today. This complements the detailed Staff report under Insights.',
                ],
                [
                    'heading' => 'When to use it',
                    'body' => 'Check Mission Control at shift start, after rush periods, and before closing to confirm revenue, throughput, and outstanding pickups.',
                ],
            ],
        ],
        [
            'title' => 'Live operations',
            'icon' => 'sparkles',
            'summary' => 'Real-time wash floor workflow from queue to checkout.',
            'topics' => [
                [
                    'heading' => 'Live board',
                    'body' => 'The Live screen shows job cards moving through the wash bay. Each card displays registration number, customer, assigned washer, services, and current stage. Supervisors can start washes and mark vehicles ready directly from the board.',
                ],
                [
                    'heading' => 'Job cards index',
                    'body' => 'The Job Cards page groups today\'s work into Open, In Progress, and Completed sections. Legacy routes (open, in-progress, completed) redirect here. Use it for a tabular view; use Live for floor operations.',
                ],
                [
                    'heading' => 'Wash workflow',
                    'body' => 'Every job card moves through three stages: Queued (open) → Washing (in progress) → Ready (completed). Starting a wash records started_at. Marking ready records completed_at and may accrue washer commission depending on company settings.',
                ],
                [
                    'heading' => 'Job card detail page',
                    'body' => 'Open any job card to see the full picture: vehicle and customer links, assigned washer, linked booking, services and products with totals, wash duration, progress stepper, and quick actions (Start Wash, Mark Ready, Checkout, Edit). Completed jobs without an invoice show a Checkout button that opens POS with the cart pre-filled.',
                ],
                [
                    'heading' => 'Creating job cards',
                    'body' => 'Select customer, vehicle, optional booking, assigned attendee, and one or more services. At least one service is required. The booking dropdown only lists linkable bookings—not completed, cancelled, or already linked ones. Offline creation is supported.',
                ],
                [
                    'heading' => 'Assigning washers',
                    'body' => 'Assign an attendee (wash staff) on the job card before completing the wash if you use commissions. Supervisors are on salary and do not earn per-wash commission even when assigned.',
                ],
                [
                    'heading' => 'After mark ready',
                    'body' => 'When a wash is marked ready, you are typically redirected to POS to collect payment. Commission may accrue at job completion, at POS checkout, or both—depending on Settings → Company.',
                ],
            ],
        ],
        [
            'title' => 'Bookings',
            'icon' => 'calendar',
            'summary' => 'Schedule and manage customer appointments.',
            'topics' => [
                [
                    'heading' => 'Daily bookings list',
                    'body' => 'All Bookings shows one day at a time (defaults to today). Pick a date, filter by status or type, and use quick links for yesterday/tomorrow. This keeps reception focused on the current schedule.',
                ],
                [
                    'heading' => 'Booking detail',
                    'body' => 'Each booking page shows schedule, customer, vehicle, services, status, and linked job card. Use Mark done on past-due confirmed bookings when the customer already visited without a formal check-in.',
                ],
                [
                    'heading' => 'Calendar',
                    'body' => 'View bookings by date and time slot to plan bay capacity and avoid overbooking.',
                ],
                [
                    'heading' => 'Walk-ins',
                    'body' => 'Register customers who arrive without an appointment. Convert walk-ins into job cards from the booking or check-in flow.',
                ],
                [
                    'heading' => 'Status lists',
                    'body' => 'Pending, Completed, and Cancelled lists help reception follow up on confirmations, no-shows, and historical visits.',
                ],
                [
                    'heading' => 'Creating bookings',
                    'body' => 'Customer phone is optional. Add services with estimated duration and price. Recurring bookings are flagged on the detail page when applicable.',
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
                    'body' => 'Record a vehicle arrival, link it to a customer, and start the service workflow. Check-in can create or link job cards.',
                ],
                [
                    'heading' => 'Registration numbers',
                    'body' => 'Registration numbers are stored and displayed in uppercase (e.g. KDJ 902K) for consistency across search, job cards, and reports.',
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
                    'body' => 'Look up past visits, services performed, and spending for any registered vehicle. Job cards link from history for full detail.',
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
                    'body' => 'Create and edit customer profiles with contact details and linked vehicles. Phone is optional. Customers are required for POS checkout and invoicing.',
                ],
                [
                    'heading' => 'Customer detail',
                    'body' => 'View visit history, vehicles, loyalty points, and notes. Jump to bookings or job cards from related records.',
                ],
                [
                    'heading' => 'Loyalty program',
                    'body' => 'After a configured number of paid washes, the next wash is free. Admins enable and set the threshold under Settings → Company (default: 10 paid washes, 11th free). The Loyalty Program page lists qualifying customers.',
                ],
                [
                    'heading' => 'Customer feedback',
                    'body' => 'Review feedback submissions to improve service quality.',
                ],
                [
                    'heading' => 'SMS notifications',
                    'body' => 'When SMS is enabled in company settings and integrations are configured, customers can receive automated texts for vehicle registration and collection.',
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
                    'heading' => 'Checkout from job card',
                    'body' => 'Open POS with ?job_card={id} or use Checkout on a completed job card. The cart pre-fills with services (and products) from that job, customer, and vehicle.',
                ],
                [
                    'heading' => 'Checkout flow',
                    'body' => '1) Select or create a customer. 2) Add items to the cart. 3) Choose a payment method. 4) Complete the sale to issue a receipt and invoice.',
                ],
                [
                    'heading' => 'Cash payments',
                    'body' => 'When cash is selected, confirm you have physically received the payment before the receipt is issued.',
                ],
                [
                    'heading' => 'M-Pesa payments',
                    'body' => 'M-Pesa triggers an STK push to the customer phone. Confirm the push succeeded before completing the sale.',
                ],
                [
                    'heading' => 'Receipts',
                    'body' => 'After checkout you are redirected to the receipt page where you can print or start a new sale.',
                ],
                [
                    'heading' => 'Commissions at checkout',
                    'body' => 'If company settings accrue commission at POS checkout (instead of or in addition to job completion), commission is calculated when the invoice is paid.',
                ],
                [
                    'heading' => 'Offline checkout',
                    'body' => 'Cash sales can be completed offline. The sale is saved locally and uploaded when connectivity returns. M-Pesa is not available offline.',
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
                    'body' => 'Group services (e.g. exterior wash, interior, detailing) for easier catalog navigation in POS and job cards.',
                ],
                [
                    'heading' => 'Services',
                    'body' => 'Define individual services with price and estimated duration. Only active services appear on job cards and POS.',
                ],
                [
                    'heading' => 'Packages',
                    'body' => 'Bundle multiple services at a combined price for upsells and promotions.',
                ],
                [
                    'heading' => 'Pricing',
                    'body' => 'Edit service prices from the Services list. Package prices are managed on the Packages screen. Price changes apply to new job cards and POS sales.',
                ],
            ],
        ],
        [
            'title' => 'Staff',
            'icon' => 'user-group',
            'summary' => 'Workforce, attendance, performance, and washer payouts.',
            'topics' => [
                [
                    'heading' => 'Employees',
                    'body' => 'Maintain staff records with contact details, employee type (Attendee or Supervisor), and optional link to a system user. Attendees appear in job card assignment lists; supervisors oversee operations.',
                ],
                [
                    'heading' => 'Employee numbers',
                    'body' => 'Employee numbers are generated automatically when not provided, keeping payroll references consistent.',
                ],
                [
                    'heading' => 'Attendance',
                    'body' => 'When enabled under Settings → Company, track who is on shift and review attendance history. Mobile staff can clock in from the mobile menu.',
                ],
                [
                    'heading' => 'Performance',
                    'body' => 'Review productivity metrics and job completion trends per employee over selectable periods.',
                ],
                [
                    'heading' => 'Commissions overview',
                    'body' => 'Attendees earn a percentage of each wash (default 30%). Supervisors do not earn per-wash commission. Enable commissions and set rate/trigger under Settings → Company.',
                ],
                [
                    'heading' => 'Daily commissions page',
                    'body' => 'Staff → Commissions shows washer payouts for a selected day: earned total, pending payout, and wash count. Pick a date to review historical days. Missing commissions for completed washes are backfilled when you open the page.',
                ],
                [
                    'heading' => 'Commission detail',
                    'body' => 'Open a commission to see wash value, rate, amount, linked job card, washer profile, and payout status.',
                ],
                [
                    'heading' => 'Paying washers',
                    'body' => 'Mark Paid records a manual settlement for all pending commissions that day for one washer. Send M-Pesa opens an OTP flow: an authorization code is sent to the admin phone (user profile or company phone). Enter the OTP to trigger B2C payout to the washer. Employee and admin phones must be set.',
                ],
                [
                    'heading' => 'Commission triggers',
                    'body' => 'Choose when commission accrues: when the job is marked ready, when POS checkout is paid, or both. Job completion is the recommended default for wash-first workflows.',
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
                    'body' => 'Manage retail and consumable products sold through POS with SKU, price, reorder level, and stock on hand. SKUs can be auto-generated.',
                ],
                [
                    'heading' => 'Suppliers & purchases',
                    'body' => 'Record suppliers and purchase orders for restocking. Track order status and received quantities.',
                ],
                [
                    'heading' => 'Stock movement',
                    'body' => 'Record stock ins and outs with timestamps. Every adjustment is logged for audit. Use this after deliveries or internal consumption.',
                ],
                [
                    'heading' => 'Low stock',
                    'body' => 'The Low Stock page and Mission Control alert highlight products at or below reorder level.',
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
                    'body' => 'View issued invoices with line items, totals, payment status, and linked job cards. Partially paid invoices show balance due.',
                ],
                [
                    'heading' => 'Receipts',
                    'body' => 'Browse receipts generated from POS and other payment flows. Open a receipt for print-friendly view.',
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
            'summary' => 'Payment channel reporting and reconciliation.',
            'topics' => [
                [
                    'heading' => 'By channel',
                    'body' => 'Cash, M-Pesa, card, and bank views list transactions for each channel. Use them to reconcile takings against physical counts and bank statements.',
                ],
                [
                    'heading' => 'Commission payouts',
                    'body' => 'M-Pesa commission payouts appear in activity logs with payment reference. Manual payouts are recorded without an external reference.',
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
                    'body' => 'Daily, weekly, and monthly summaries show revenue and activity trends. Use date filters to compare periods.',
                ],
                [
                    'heading' => 'Revenue report',
                    'body' => 'Filter by From and To dates for a full income breakdown: collected vs billed, today/week/month snapshots, outstanding balances, payment method split, services vs products, daily trend, top items, and recent invoices. Click This Month to reset to the current month.',
                ],
                [
                    'heading' => 'Customer report',
                    'body' => 'Analytical dashboard with date filters: new and active customers, period revenue, repeat rate, visit-frequency segments, fleet size, six-month acquisition trend, top spenders, new registrations, and at-risk customers (no visit in 60 days).',
                ],
                [
                    'heading' => 'Staff report',
                    'body' => 'Team performance for a date range: jobs completed, in-progress workload, assigned revenue, average jobs per staff, completion times, and commissions when enabled. Includes leaderboard, weekly trend, and underutilized staff.',
                ],
                [
                    'heading' => 'Job cards report',
                    'body' => 'Pick a report date for open, in progress, completed, and total counts, plus week/month summaries and a detailed list of cards active that day.',
                ],
                [
                    'heading' => 'Inventory report',
                    'body' => 'Set stock position as-of datetime and a movements date range to audit on-hand quantities, low stock, stock value, and recent stock ins.',
                ],
            ],
        ],
        [
            'title' => 'Mobile cockpit',
            'icon' => 'phone_iphone',
            'summary' => 'Floor-friendly interface for staff on phones and tablets.',
            'topics' => [
                [
                    'heading' => 'Accessing mobile',
                    'body' => 'Staff with a PIN sign in via the PIN tab and land on the mobile dashboard. Supervisors and admins can also open /mobile while signed in. Add the site to your home screen for an app-like experience.',
                ],
                [
                    'heading' => 'Mobile dashboard',
                    'body' => 'Shows today\'s key stats, recent activity, and shortcuts to Live, job cards, bookings, and POS—matching Mission Control in a compact layout.',
                ],
                [
                    'heading' => 'Live & job cards',
                    'body' => 'Update wash status from the mobile live board. Create job cards and view details. Full job card management links to the desktop detail page when needed.',
                ],
                [
                    'heading' => 'Mobile menu',
                    'body' => 'The menu exposes modules your role allows: bookings, customers, vehicles, POS, commissions, attendance, and more—filtered by permissions.',
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
            'title' => 'Activity & audit',
            'icon' => 'history',
            'summary' => 'Automatic logging of system actions.',
            'topics' => [
                [
                    'heading' => 'What is logged',
                    'body' => 'AutoSpa records sign-ins, branch switches, job card and booking changes, sales, commission payouts, settings updates, offline sync mutations, and other key events. Passwords, PINs, and OTP codes are never stored in logs.',
                ],
                [
                    'heading' => 'Where to view activity',
                    'body' => 'Recent Activity on Mission Control and the mobile dashboard shows the latest entries for your branch. Each line includes who performed the action and when.',
                ],
                [
                    'heading' => 'Reliability',
                    'body' => 'Activity logging runs in the background. If a log write fails, the underlying operation (checkout, wash completion, etc.) still succeeds.',
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
                    'body' => 'Set legal name, contact details, and branding. Configure SMS notifications, washer commissions (enable, rate %, trigger), loyalty program (enable, washes before free), and attendance tracking.',
                ],
                [
                    'heading' => 'Branches',
                    'body' => 'Add and manage branch locations. Users can be assigned to a home branch.',
                ],
                [
                    'heading' => 'Business hours',
                    'body' => 'Define opening hours per day of week under Settings → Business hours (direct URL). Useful for booking capacity and customer communications.',
                ],
                [
                    'heading' => 'Users & roles',
                    'body' => 'Create user accounts, assign Admin or Supervisor roles, set optional PINs for mobile login, and control module access through permissions.',
                ],
                [
                    'heading' => 'Integrations',
                    'body' => 'Connect M-Pesa (STK and B2C for commission payouts), SMS, email, and other providers. Credentials are stored securely; test before going live.',
                ],
                [
                    'heading' => 'Test Ground',
                    'body' => 'Under Settings → Test Ground, send test emails, SMS, WhatsApp messages, and M-Pesa STK pushes to verify configuration.',
                ],
                [
                    'heading' => 'Email notifications',
                    'body' => 'System email delivery can be enabled or disabled on the server using php artisan notifications:email enable|disable|status. When disabled, outbound emails are suppressed without changing other settings.',
                ],
            ],
        ],
    ],
];
