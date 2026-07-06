<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncMutation extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'client_mutation_id';

    public $timestamps = false;

    protected $fillable = [
        'client_mutation_id',
        'user_id',
        'branch_id',
        'type',
        'entity_uuid',
        'result',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
