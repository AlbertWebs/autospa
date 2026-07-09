<?php

namespace App\Support;

use Illuminate\Pagination\AbstractPaginator;

class TableRowNumber
{
    public static function for(mixed $loop, ?AbstractPaginator $paginator = null, int $offset = 0): int
    {
        if ($paginator instanceof AbstractPaginator && $paginator->firstItem() !== null) {
            return $paginator->firstItem() + $loop->index;
        }

        return $offset + $loop->iteration;
    }
}
