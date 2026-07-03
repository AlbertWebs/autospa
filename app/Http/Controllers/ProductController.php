<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('products.index', [
            'products' => Product::query()->with('supplier')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($this->withBranchId($request->validated()));

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created.');
    }

    public function show(Product $product): View
    {
        return view('products.show', [
            'product' => $product->load(['supplier', 'stockMovements' => fn ($q) => $q->latest()->limit(10)]),
        ]);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted.');
    }

    public function lowStock(): View
    {
        return view('products.low-stock', [
            'products' => Product::query()
                ->with('supplier')
                ->whereColumn('quantity_on_hand', '<=', 'minimum_level')
                ->orderBy('quantity_on_hand')
                ->paginate(15),
        ]);
    }
}
