<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\View\View;

class MobilePurchaseOrderController extends Controller
{
    public function index(): View
    {
        return view('mobile.purchase-orders.index', [
            'purchaseOrders' => PurchaseOrder::query()->with('supplier')->latest()->paginate(20),
        ]);
    }
}
