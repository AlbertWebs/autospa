<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('stock-movements.index', [
            'movements' => StockMovement::query()
                ->with(['product', 'user'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('stock-movements.create', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStockMovementRequest $request): RedirectResponse
    {
        StockMovement::create([
            ...$this->withBranchId($request->validated()),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stock movement recorded.');
    }

    public function show(StockMovement $stockMovement): View
    {
        return view('stock-movements.show', [
            'movement' => $stockMovement->load(['product', 'user']),
        ]);
    }
}
