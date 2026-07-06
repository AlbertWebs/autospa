<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\View\View;

class MobileStockMovementController extends Controller
{
    public function index(): View
    {
        return view('mobile.stock-movements.index', [
            'stockMovements' => StockMovement::query()->with(['product', 'user'])->latest()->paginate(20),
        ]);
    }
}
