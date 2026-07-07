<?php

namespace App\Http\Controllers\Concerns;

trait AssignsBranchId
{
    protected function withBranchId(array $data): array
    {
        $data['branch_id'] = session('current_branch_id') ?? auth()->user()?->branch_id;

        return $data;
    }
}
