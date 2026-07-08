<?php

namespace App\Http\Controllers;

use App\Enums\FixedAssetCategory;
use App\Enums\FixedAssetStatus;
use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreFixedAssetRequest;
use App\Http\Requests\UpdateFixedAssetRequest;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FixedAssetController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        $assets = FixedAsset::query()
            ->with(['supplier', 'assignee'])
            ->latest()
            ->paginate(15);

        $activeValue = (float) FixedAsset::query()
            ->where('status', FixedAssetStatus::Active)
            ->sum('purchase_cost');

        return view('fixed-assets.index', [
            'assets' => $assets,
            'activeValue' => $activeValue,
        ]);
    }

    public function create(): View
    {
        return view('fixed-assets.create', $this->formData());
    }

    public function store(StoreFixedAssetRequest $request): RedirectResponse
    {
        $asset = FixedAsset::create($this->withBranchId($request->validated()));

        return redirect()->route('fixed-assets.show', $asset)
            ->with('success', 'Fixed asset recorded.');
    }

    public function show(FixedAsset $fixedAsset): View
    {
        return view('fixed-assets.show', [
            'asset' => $fixedAsset->load(['supplier', 'assignee']),
        ]);
    }

    public function edit(FixedAsset $fixedAsset): View
    {
        return view('fixed-assets.edit', array_merge(
            ['asset' => $fixedAsset],
            $this->formData(),
        ));
    }

    public function update(UpdateFixedAssetRequest $request, FixedAsset $fixedAsset): RedirectResponse
    {
        $fixedAsset->update($request->validated());

        return redirect()->route('fixed-assets.show', $fixedAsset)
            ->with('success', 'Fixed asset updated.');
    }

    public function destroy(FixedAsset $fixedAsset): RedirectResponse
    {
        $fixedAsset->delete();

        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed asset deleted.');
    }

    /** @return array<string, mixed> */
    protected function formData(): array
    {
        return [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::query()->where('is_active', true)->orderBy('full_name')->get(),
            'categories' => FixedAssetCategory::cases(),
            'statuses' => FixedAssetStatus::cases(),
            'nextAssetTag' => FixedAsset::generateAssetTag(),
        ];
    }
}
