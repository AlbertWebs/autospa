<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use BelongsToBranch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'branch_id',
        'invoice_id',
        'customer_id',
        'received_by',
        'payment_method_id',
        'method',
        'amount',
        'status',
        'reference',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethodType::class,
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function splits(): HasMany
    {
        return $this->hasMany(PaymentSplit::class);
    }
}
