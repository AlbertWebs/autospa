<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Collection;

class BranchService
{
    public function availableForUser(User $user): Collection
    {
        if ($user->isSuperAdmin()) {
            return Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        if ($user->branch_id) {
            return Branch::query()->where('id', $user->branch_id)->where('is_active', true)->get();
        }

        return collect();
    }

    public function currentBranchId(): ?int
    {
        $id = session('current_branch_id') ?? auth()->user()?->branch_id;

        return $id !== null ? (int) $id : null;
    }

    public function currentBranch(): ?Branch
    {
        $id = $this->currentBranchId();

        return $id ? Branch::find($id) : null;
    }

    public function switchBranch(User $user, Branch $branch): void
    {
        if (! $user->isSuperAdmin() && $user->branch_id !== $branch->id) {
            abort(403, 'You cannot access this branch.');
        }

        session(['current_branch_id' => $branch->id]);
    }

    public function ensureBranchSelected(User $user): void
    {
        if (session('current_branch_id')) {
            return;
        }

        $branches = $this->availableForUser($user);

        if ($branches->isNotEmpty()) {
            session(['current_branch_id' => $branches->first()->id]);

            return;
        }

        if ($user->branch_id) {
            session(['current_branch_id' => $user->branch_id]);
        }
    }
}
