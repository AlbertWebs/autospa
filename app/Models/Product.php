<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use App\Services\StockMovementService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use BelongsToBranch, HasFactory, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (blank($product->sku)) {
                $product->sku = static::generateSku($product->branch_id);
            }
        });
    }

    public static function generateSku(?int $branchId = null): string
    {
        $branchId ??= session('current_branch_id');

        $query = static::withTrashed()->where('sku', 'like', 'SKU-%');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $max = $query
            ->pluck('sku')
            ->map(fn (string $sku) => (int) Str::after($sku, 'SKU-'))
            ->max();

        $next = ($max ?? 0) + 1;

        return 'SKU-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'uuid',
        'branch_id',
        'supplier_id',
        'sku',
        'name',
        'description',
        'unit',
        'cost_price',
        'selling_price',
        'quantity_on_hand',
        'minimum_level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'quantity_on_hand' => 'decimal:2',
            'minimum_level' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockBalanceAsOf(Carbon $at): float
    {
        return app(StockMovementService::class)->stockBalanceAsOf($this, $at);
    }
}
