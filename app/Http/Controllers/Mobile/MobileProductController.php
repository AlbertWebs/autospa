<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class MobileProductController extends Controller
{
    public function index(): View
    {
        return view('mobile.products.index', [
            'products' => Product::query()->with('supplier')->latest()->paginate(20),
        ]);
    }

    public function lowStock(): View
    {
        return view('mobile.products.low-stock', [
            'products' => Product::query()
                ->with('supplier')
                ->whereColumn('quantity_on_hand', '<=', 'minimum_level')
                ->orderBy('quantity_on_hand')
                ->paginate(20),
        ]);
    }

    public function show(Product $product): View
    {
        return view('mobile.products.show', [
            'product' => $product->load('supplier'),
        ]);
    }
}
