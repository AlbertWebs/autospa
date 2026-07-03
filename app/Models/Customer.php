<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToBranch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'branch_id',
        'full_name',
        'phone',
        'email',
        'id_number',
        'address',
        'vehicle_count',
        'total_visits',
        'lifetime_spending',
        'loyalty_points',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_count' => 'integer',
            'total_visits' => 'integer',
            'lifetime_spending' => 'decimal:2',
            'loyalty_points' => 'integer',
        ];
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
