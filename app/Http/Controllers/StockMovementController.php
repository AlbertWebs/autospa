<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    use AssignsBranchId;

    public function __construct(
        protected StockMovementService $stockMovementService,
    ) {}

    public function index(): View
    {
        return view('stock-movements.index', [
            'movements' => StockMovement::query()
                ->with(['product', 'user'])
                ->orderByDesc('moved_at')
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }

    public function store(StoreStockMovementRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $returnTo = $request->input('return_to') === 'products' ? 'products.index' : 'stock-movements.index';

        unset($validated['return_to']);

        $validated['moved_at'] = Carbon::parse($validated['moved_at']);

        $this->stockMovementService->recordMovement(
            $this->withBranchId($validated),
            $request->user()->id,
        );

        $message = $validated['type'] === 'in'
            ? 'Stock added successfully.'
            : 'Stock movement recorded.';

        if ($request->expectsJson()) {
            $product = Product::query()->findOrFail($validated['product_id']);

            return response()->json([
                'message' => $message,
                'redirect' => route($returnTo),
                'product' => [
                    'id' => $product->id,
                    'quantity_on_hand' => (float) $product->quantity_on_hand,
                ],
            ]);
        }

        return redirect()->route($returnTo)->with('success', $message);
    }

    public function show(StockMovement $stockMovement): View
    {
        return view('stock-movements.show', [
            'movement' => $stockMovement->load(['product', 'user']),
        ]);
    }
}
