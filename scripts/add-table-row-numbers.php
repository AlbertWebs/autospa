<?php

/**
 * Add row numbering cells to data-table listing views.
 * Run: php scripts/add-table-row-numbers.php
 */

$root = dirname(__DIR__);
$viewsPath = $root.'/resources/views';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

$updated = 0;

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();

    if (! str_contains(file_get_contents($path), 'x-ui.data-table')) {
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    $content = preg_replace('/\s*<x-ui\.th>\s*#\s*<\/x-ui\.th>\s*/', "\n", $content);

    $paginatorVar = null;
    if (preg_match('/:paginator="\$([A-Za-z0-9_]+)"/', $content, $matches)) {
        $paginatorVar = $matches[1];
    }

    $numberCell = $paginatorVar
        ? '<x-ui.table-number-td :loop="$loop" :paginator="$'.$paginatorVar.'" />'
        : '<x-ui.table-number-td :loop="$loop" />';

    $content = preg_replace(
        '/<tr class="asp-table-row">\s*\n\s*<x-ui\.td[^>]*>\s*(?:\{\{\s*\$index\s*\+\s*1\s*\}\}|#\{\{\s*\$[A-Za-z0-9_]+->id\s*\}\})\s*<\/x-ui\.td>/',
        "<tr class=\"asp-table-row\">\n                {$numberCell}",
        $content
    );

    $content = preg_replace(
        '/(<tr class="asp-table-row">)\s*\n(?!\s*<x-ui\.table-number-td)/',
        "$1\n                {$numberCell}\n",
        $content
    );

    if ($content !== $original) {
        file_put_contents($path, $content);
        $updated++;
        echo 'Updated: '.str_replace($root.DIRECTORY_SEPARATOR, '', $path).PHP_EOL;
    }
}

echo "Done. Updated {$updated} files.".PHP_EOL;
