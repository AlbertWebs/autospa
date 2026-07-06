<?php

namespace App\Models;

use App\Enums\RoleSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->whereIn('slug', RoleSlug::values());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("CASE slug WHEN 'super_admin' THEN 0 WHEN 'manager' THEN 1 ELSE 2 END")
            ->orderBy('name');
    }
}
