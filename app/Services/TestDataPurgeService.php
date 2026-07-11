<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestDataPurgeService
{
    /**
     * @return array<string, array{label: string, description: string, tables: list<string>, count: int}>
     */
    public function catalog(): array
    {
        $groups = $this->groups();

        foreach ($groups as $key => &$group) {
            $group['count'] = $this->countRows($group['tables']);
        }
        unset($group);

        return $groups;
    }

    /**
     * @param  list<string>  $keys
     * @return array{deleted: array<string, int>, groups: list<string>}
     */
    public function purge(array $keys): array
    {
        $catalog = $this->groups();
        $selected = array_values(array_intersect(array_keys($catalog), $keys));

        if ($selected === []) {
            return ['deleted' => [], 'groups' => []];
        }

        $deleted = [];

        Schema::withoutForeignKeyConstraints(function () use ($selected, $catalog, &$deleted): void {
            foreach ($selected as $key) {
                foreach ($catalog[$key]['tables'] as $table) {
                    if (! Schema::hasTable($table)) {
                        continue;
                    }

                    $deleted[$table] = ($deleted[$table] ?? 0) + DB::table($table)->delete();
                }
            }
        });

        return [
            'deleted' => $deleted,
            'groups' => $selected,
        ];
    }

    /**
     * @return array<string, array{label: string, description: string, tables: list<string>}>
     */
    protected function groups(): array
    {
        return [
            'job_cards' => [
                'label' => 'Job cards',
                'description' => 'Open and completed job cards, line items, photos, and checklists.',
                'tables' => [
                    'job_card_checklist_items',
                    'job_card_photos',
                    'job_card_products',
                    'job_card_services',
                    'job_cards',
                ],
            ],
            'bookings' => [
                'label' => 'Bookings',
                'description' => 'Appointments, booking services, and recurring booking rules.',
                'tables' => [
                    'booking_services',
                    'recurring_booking_rules',
                    'bookings',
                ],
            ],
            'sales' => [
                'label' => 'Payments & invoices',
                'description' => 'Payments, splits, invoices, receipts, refunds, and M-Pesa transactions.',
                'tables' => [
                    'payment_splits',
                    'payments',
                    'receipts',
                    'invoice_items',
                    'invoices',
                    'mpesa_transactions',
                ],
            ],
            'customers_vehicles' => [
                'label' => 'Customers & vehicles',
                'description' => 'Customers, notes, loyalty history, vehicles, and vehicle photos.',
                'tables' => [
                    'loyalty_transactions',
                    'customer_notes',
                    'vehicle_photos',
                    'vehicles',
                    'customers',
                ],
            ],
            'staff_activity' => [
                'label' => 'Staff activity',
                'description' => 'Attendance, commissions, and performance metrics. Keeps employee profiles.',
                'tables' => [
                    'attendance',
                    'commissions',
                    'performance_metrics',
                ],
            ],
            'inventory_activity' => [
                'label' => 'Inventory activity',
                'description' => 'Purchase orders and stock movements. Keeps products and suppliers.',
                'tables' => [
                    'stock_movements',
                    'purchase_order_items',
                    'purchase_orders',
                ],
            ],
            'finance' => [
                'label' => 'Finance records',
                'description' => 'Manual expenses and account closures.',
                'tables' => [
                    'expenses',
                    'finance_account_closures',
                ],
            ],
            'logs' => [
                'label' => 'Logs & sync queue',
                'description' => 'Activity log, offline sync mutations, report snapshots, and notifications.',
                'tables' => [
                    'activity_log',
                    'sync_mutations',
                    'report_snapshots',
                    'notifications',
                ],
            ],
        ];
    }

    /**
     * @param  list<string>  $tables
     */
    protected function countRows(array $tables): int
    {
        $total = 0;

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $total += (int) DB::table($table)->count();
        }

        return $total;
    }
}
