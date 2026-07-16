<?php
// Import the vehicles table from the SQLite dump, skipping duplicates that
// collide on MySQL's case-insensitive (branch_id, registration_number) unique
// index. Rows referenced by job cards/bookings win; otherwise the row with
// the most complete data wins.
// Usage: php deploy/import-vehicles.php /path/to/deploy.sqlite

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['database.connections.import_sqlite' => [
    'driver' => 'sqlite',
    'database' => $argv[1],
    'foreign_key_constraints' => false,
]]);

$sqlite = DB::connection('import_sqlite');
$mysql = DB::connection();

$referenced = $sqlite->table('job_cards')->whereNotNull('vehicle_id')->pluck('vehicle_id')
    ->merge($sqlite->table('bookings')->whereNotNull('vehicle_id')->pluck('vehicle_id'))
    ->unique()
    ->all();

$columns = Schema::getColumnListing('vehicles');
$rows = $sqlite->table('vehicles')->get();

$score = function ($row) use ($referenced) {
    $filled = count(array_filter((array) $row, fn ($v) => $v !== null && $v !== ''));

    return (in_array($row->id, $referenced) ? 1000 : 0) + $filled;
};

$byKey = [];

foreach ($rows as $row) {
    $key = $row->branch_id.'|'.mb_strtolower(trim($row->registration_number));

    if (! isset($byKey[$key]) || $score($row) > $score($byKey[$key])) {
        $byKey[$key] = $row;
    }
}

$mysql->statement('SET FOREIGN_KEY_CHECKS=0');
$mysql->table('vehicles')->truncate();

$inserted = 0;
$skipped = [];

foreach ($rows as $row) {
    $key = $row->branch_id.'|'.mb_strtolower(trim($row->registration_number));

    if ($byKey[$key]->id !== $row->id) {
        $skipped[] = "#{$row->id} '{$row->registration_number}' (duplicate of #{$byKey[$key]->id})";
        continue;
    }

    $mysql->table('vehicles')->insert(array_intersect_key((array) $row, array_flip($columns)));
    $inserted++;
}

$mysql->statement('SET FOREIGN_KEY_CHECKS=1');

echo "vehicles inserted: {$inserted}\n";

foreach ($skipped as $line) {
    echo "skipped: {$line}\n";
}
