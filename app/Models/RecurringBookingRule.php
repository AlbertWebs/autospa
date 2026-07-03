<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringBookingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'frequency',
        'interval',
        'ends_on',
        'occurrences',
        'days_of_week',
    ];

    protected function casts(): array
    {
        return [
            'interval' => 'integer',
            'ends_on' => 'date',
            'occurrences' => 'integer',
            'days_of_week' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
