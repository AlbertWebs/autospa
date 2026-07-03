<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('promotions.index', [
            'promotions' => Promotion::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('promotions.create');
    }

    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $promotion = Promotion::create($this->withBranchId($request->validated()));

        return redirect()->route('promotions.show', $promotion)
            ->with('success', 'Promotion created.');
    }

    public function show(Promotion $promotion): View
    {
        return view('promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion): View
    {
        return view('promotions.edit', compact('promotion'));
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->validated());

        return redirect()->route('promotions.show', $promotion)
            ->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('promotions.index')
            ->with('success', 'Promotion deleted.');
    }
}
