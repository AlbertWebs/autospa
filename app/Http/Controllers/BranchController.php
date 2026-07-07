<?php

namespace App\Http\Controllers;

use App\Enums\ActivityEvent;
use App\Models\Branch;
use App\Services\ActivityLogService;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        protected BranchService $branchService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        $this->authorize('view', $branch);

        $previousBranchId = session('current_branch_id');

        $this->branchService->switchBranch($request->user(), $branch);

        $this->activityLogService->record(
            ActivityEvent::BranchSwitched->value,
            "Switched branch to {$branch->name}",
            $branch,
            [
                'from_branch_id' => $previousBranchId,
                'to_branch_id' => $branch->id,
            ],
            $request->user()->id,
            $branch->id,
        );

        return back()->with('success', "Switched to {$branch->name}");
    }
}
