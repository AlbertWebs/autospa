<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Commission extends Model
{
    use BelongsToBranch, HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'reference_type',
        'reference_id',
        'amount',
        'rate',
        'status',
        'earned_on',
        'trigger_event',
        'paid_at',
        'payment_method',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate' => 'decimal:4',
            'earned_on' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
