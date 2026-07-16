<?php
// Usage: php sqlite-inspect.php <path-to-sqlite>
$pdo = new PDO('sqlite:'.$argv[1]);

$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM \"$table\"")->fetchColumn();

    if ($count > 0) {
        echo str_pad($table, 42).$count.PHP_EOL;
    }
}
