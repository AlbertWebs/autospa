<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesJobCardServices
{
    /**
     * @return array<string, mixed>
     */
    protected function jobCardServiceRules(bool $required = true): array
    {
        $branchId = session('current_branch_id');

        $serviceIdRule = Rule::exists('services', 'id')
            ->where('is_active', true)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        return [
            'service_ids' => $required
                ? ['required', 'array', 'min:1']
                : ['sometimes', 'array', 'min:1'],
            'service_ids.*' => ['required', 'integer', $serviceIdRule],
        ];
    }
}
