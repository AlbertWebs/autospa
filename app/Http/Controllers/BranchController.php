<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        protected BranchService $branchService,
    ) {}

    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        $this->authorize('view', $branch);
        $this->branchService->switchBranch($request->user(), $branch);

        return back()->with('success', "Switched to {$branch->name}");
    }
}
