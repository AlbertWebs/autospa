<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Models\Setting;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.taxes.index', [
            'taxes' => Tax::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('update', Setting::class);

        return view('settings.taxes.create');
    }

    public function store(StoreTaxRequest $request): RedirectResponse
    {
        Tax::create($this->withBranchId($request->validated()));

        return redirect()->route('settings.taxes.index')
            ->with('success', 'Tax created.');
    }

    public function show(Tax $tax): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.taxes.show', compact('tax'));
    }

    public function edit(Tax $tax): View
    {
        $this->authorize('update', Setting::class);

        return view('settings.taxes.edit', compact('tax'));
    }

    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        $tax->update($request->validated());

        return redirect()->route('settings.taxes.index')
            ->with('success', 'Tax updated.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $tax->delete();

        return redirect()->route('settings.taxes.index')
            ->with('success', 'Tax deleted.');
    }
}
