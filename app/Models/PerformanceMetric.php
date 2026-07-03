<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceMetric extends Model
{
    use BelongsToBranch, HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'period_start',
        'period_end',
        'jobs_completed',
        'revenue_generated',
        'average_rating',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'jobs_completed' => 'integer',
            'revenue_generated' => 'decimal:2',
            'average_rating' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
