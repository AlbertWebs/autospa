<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use BelongsToBranch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'branch_id',
        'customer_id',
        'vehicle_id',
        'created_by',
        'type',
        'status',
        'scheduled_at',
        'ends_at',
        'notes',
        'is_recurring',
    ];

    protected function casts(): array
    {
        return [
            'type' => BookingType::class,
            'status' => BookingStatus::class,
            'scheduled_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_recurring' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function recurringRule(): HasOne
    {
        return $this->hasOne(RecurringBookingRule::class);
    }

    public function jobCard(): HasOne
    {
        return $this->hasOne(JobCard::class);
    }
}
