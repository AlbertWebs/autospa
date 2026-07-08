<?php

namespace App\Models;

use App\Enums\FixedAssetCategory;
use App\Enums\FixedAssetStatus;
use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FixedAsset extends Model
{
    use BelongsToBranch, HasFactory, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (FixedAsset $asset) {
            if (blank($asset->asset_tag)) {
                $asset->asset_tag = static::generateAssetTag($asset->branch_id);
            }
        });
    }

    public static function generateAssetTag(?int $branchId = null): string
    {
        $branchId ??= session('current_branch_id');

        $query = static::withTrashed()->where('asset_tag', 'like', 'AST-%');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $max = $query
            ->pluck('asset_tag')
            ->map(fn (string $tag) => (int) Str::after($tag, 'AST-'))
            ->max();

        $next = ($max ?? 0) + 1;

        return 'AST-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'uuid',
        'branch_id',
        'supplier_id',
        'assigned_employee_id',
        'asset_tag',
        'name',
        'category',
        'description',
        'location',
        'purchase_date',
        'purchase_cost',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => FixedAssetCategory::class,
            'status' => FixedAssetStatus::class,
            'purchase_date' => 'date',
            'purchase_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }
}
