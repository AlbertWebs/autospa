<?php
// Import all data from an SQLite file into the live MySQL database.
// Usage: php deploy/sqlite-to-mysql.php /path/to/deploy.sqlite
// Run from the Laravel root on the server. Replaces MySQL table contents.

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sqlitePath = $argv[1] ?? null;

if (! $sqlitePath || ! is_file($sqlitePath)) {
    fwrite(STDERR, "SQLite file not found: {$sqlitePath}\n");
    exit(1);
}

config(['database.connections.import_sqlite' => [
    'driver' => 'sqlite',
    'database' => $sqlitePath,
    'foreign_key_constraints' => false,
]]);

// Transient/framework tables that must not be copied.
$skip = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

$sqlite = DB::connection('import_sqlite');
$mysql = DB::connection();

$tables = $sqlite->table('sqlite_master')
    ->where('type', 'table')
    ->where('name', 'not like', 'sqlite_%')
    ->orderBy('name')
    ->pluck('name');

$mysql->statement('SET FOREIGN_KEY_CHECKS=0');

foreach ($tables as $table) {
    if (in_array($table, $skip, true)) {
        echo str_pad($table, 42)."skipped (transient)\n";
        continue;
    }

    if (! Schema::hasTable($table)) {
        echo str_pad($table, 42)."skipped (missing in MySQL)\n";
        continue;
    }

    $targetColumns = Schema::getColumnListing($table);
    $rows = $sqlite->table($table)->get();

    $mysql->table($table)->truncate();

    $inserted = 0;

    foreach ($rows->chunk(200) as $chunk) {
        $payload = [];

        foreach ($chunk as $row) {
            $data = array_intersect_key((array) $row, array_flip($targetColumns));
            $payload[] = $data;
        }

        if ($payload !== []) {
            $mysql->table($table)->insert($payload);
            $inserted += count($payload);
        }
    }

    echo str_pad($table, 42)."{$inserted} rows\n";
}

$mysql->statement('SET FOREIGN_KEY_CHECKS=1');

echo "\nImport complete.\n";
