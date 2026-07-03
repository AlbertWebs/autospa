<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'group',
        'key',
        'value',
        'type',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public static function getValue(string $group, string $key, mixed $default = null, ?int $branchId = null): mixed
    {
        $setting = static::query()
            ->where('group', $group)
            ->where('key', $key)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(! $branchId, fn ($q) => $q->whereNull('branch_id'))
            ->first();

        return $setting?->value ?? $default;
    }

    public static function setValue(string $group, string $key, mixed $value, ?int $branchId = null, string $type = 'string'): void
    {
        static::updateOrCreate(
            [
                'branch_id' => $branchId,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
            ]
        );
    }
}
