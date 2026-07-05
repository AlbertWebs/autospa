<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function recordMovement(array $data, ?int $userId): StockMovement
    {
        return DB::transaction(function () use ($data, $userId) {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);

            $movement = StockMovement::create([
                ...$data,
                'user_id' => $userId,
                'balance_after' => 0,
            ]);

            $this->recalculateProductStock($product);

            return $movement->fresh(['product', 'user']);
        });
    }

    public function recalculateProductStock(Product $product): void
    {
        $balance = 0.0;

        foreach ($this->orderedMovements($product)->get() as $movement) {
            $balance = $this->balanceAfterMovement($balance, $movement);
            $movement->forceFill(['balance_after' => $balance])->saveQuietly();
        }

        $product->update(['quantity_on_hand' => $balance]);
    }

    public function stockBalanceAsOf(Product $product, Carbon $at): float
    {
        $movement = $product->stockMovements()
            ->where('moved_at', '<=', $at)
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->first();

        return $movement ? (float) $movement->balance_after : 0.0;
    }

    public function balanceAfterMovement(float $current, StockMovement $movement): float
    {
        return match ($movement->type) {
            'in' => $current + (float) $movement->quantity,
            'out' => max(0, $current - (float) $movement->quantity),
            'adjustment' => (float) $movement->quantity,
            default => $current,
        };
    }

    protected function orderedMovements(Product $product)
    {
        return $product->stockMovements()
            ->orderBy('moved_at')
            ->orderBy('id');
    }
}
