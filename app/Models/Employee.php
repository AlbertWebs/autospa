<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Employee extends Model
{
    use BelongsToBranch, HasFactory, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (blank($employee->employee_number)) {
                $employee->employee_number = static::generateEmployeeNumber();
            }
        });
    }

    public static function generateEmployeeNumber(): string
    {
        $max = static::withTrashed()
            ->where('employee_number', 'like', 'EMP-%')
            ->pluck('employee_number')
            ->map(fn (string $number) => (int) Str::after($number, 'EMP-'))
            ->max();

        $next = ($max ?? 0) + 1;

        return 'EMP-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'uuid',
        'branch_id',
        'user_id',
        'employee_number',
        'full_name',
        'phone',
        'email',
        'position',
        'base_salary',
        'hire_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'hire_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(PerformanceMetric::class);
    }

    public function assignedJobCards(): HasMany
    {
        return $this->hasMany(JobCard::class, 'assigned_to');
    }

    public function scopeAssignableToJobCards($query, ?int $branchId = null): void
    {
        $query
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('full_name');
    }

    public function displayName(): string
    {
        return $this->position
            ? "{$this->full_name} ({$this->position})"
            : $this->full_name;
    }
}
