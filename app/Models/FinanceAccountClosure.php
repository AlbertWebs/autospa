<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceAccountClosure extends Model
{
    use BelongsToBranch, HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'branch_id',
        'from_date',
        'to_date',
        'income_total',
        'expense_total',
        'net_profit',
        'meta',
        'closed_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'income_total' => 'decimal:2',
            'expense_total' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'meta' => 'array',
            'closed_at' => 'datetime',
        ];
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
