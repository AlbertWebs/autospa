<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Branch::class);

        return view('settings.branches.index', [
            'branches' => Branch::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Branch::class);

        return view('settings.branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        Branch::create($request->validated());

        return redirect()->route('settings.branches.index')
            ->with('success', 'Branch created.');
    }

    public function show(Branch $branch): View
    {
        $this->authorize('view', $branch);

        return view('settings.branches.show', compact('branch'));
    }

    public function edit(Branch $branch): View
    {
        $this->authorize('update', $branch);

        return view('settings.branches.edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        return redirect()->route('settings.branches.index')
            ->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->authorize('delete', $branch);

        $branch->delete();

        return redirect()->route('settings.branches.index')
            ->with('success', 'Branch deleted.');
    }
}
