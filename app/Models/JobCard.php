<?php

namespace App\Models;

use App\Enums\JobCardStatus;
use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCard extends Model
{
    use BelongsToBranch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'branch_id',
        'customer_id',
        'vehicle_id',
        'booking_id',
        'assigned_to',
        'status',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobCardStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function services(): HasMany
    {
        return $this->hasMany(JobCardService::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(JobCardProduct::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(JobCardPhoto::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(JobCardChecklistItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
