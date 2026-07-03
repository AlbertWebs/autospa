<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('purchase-orders.index', [
            'purchaseOrders' => PurchaseOrder::query()->with('supplier')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('purchase-orders.create', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $order = PurchaseOrder::create([
            ...$this->withBranchId($request->safe()->except('items')),
            'created_by' => $request->user()->id,
        ]);

        if ($items = $request->validated('items')) {
            $order->items()->createMany($items);
        }

        return redirect()->route('purchase-orders.show', $order)
            ->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        return view('purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items', 'creator']),
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        return view('purchase-orders.edit', [
            'purchaseOrder' => $purchaseOrder->load('items'),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->update($request->safe()->except('items'));

        if ($request->has('items')) {
            $purchaseOrder->items()->delete();
            $purchaseOrder->items()->createMany($request->validated('items', []));
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted.');
    }
}
