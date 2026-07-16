<?php
$pdo = new PDO('sqlite:'.$argv[1]);

echo "vehicles:\n";
foreach ($pdo->query('SELECT id, customer_id, registration_number, make, model, color, created_at FROM vehicles ORDER BY id') as $r) {
    echo "  #{$r['id']} cust={$r['customer_id']} reg='{$r['registration_number']}' {$r['make']} {$r['model']} {$r['color']} {$r['created_at']}\n";
}

foreach (['job_cards', 'bookings'] as $table) {
    echo "$table vehicle refs:\n";
    foreach ($pdo->query("SELECT id, vehicle_id FROM $table ORDER BY id") as $r) {
        echo "  #{$r['id']} -> vehicle {$r['vehicle_id']}\n";
    }
}
