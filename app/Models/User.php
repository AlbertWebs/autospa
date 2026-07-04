<?php

namespace App\Models;

use App\Enums\RoleSlug;
use App\Models\Concerns\HasUuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuid, Notifiable, SoftDeletes;

    protected ?Collection $resolvedPermissionSlugs = null;

    protected $fillable = [
        'uuid',
        'branch_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleSlug::SuperAdmin->value);
    }

    public function canAccessBackoffice(): bool
    {
        return $this->hasRole(RoleSlug::SuperAdmin->value)
            || $this->hasRole(RoleSlug::Manager->value);
    }

    public function hasRole(string $slug): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn (Role $role) => $role->slug === $slug);
        }

        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissionSlugs()->contains($slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissionSlugs()->intersect($slugs)->isNotEmpty();
    }

    public function hasAllPermissions(array $slugs): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return collect($slugs)->diff($this->permissionSlugs())->isEmpty();
    }

    public function permissionSlugs(): Collection
    {
        if ($this->resolvedPermissionSlugs instanceof Collection) {
            return $this->resolvedPermissionSlugs;
        }

        if ($this->relationLoaded('roles')) {
            $this->roles->loadMissing('permissions');

            return $this->resolvedPermissionSlugs = $this->roles
                ->flatMap(fn (Role $role) => $role->permissions->pluck('slug'))
                ->unique()
                ->values();
        }

        return $this->resolvedPermissionSlugs = $this->roles()
            ->with('permissions:id,slug')
            ->get()
            ->flatMap(fn (Role $role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values();
    }

    public function needsOnboarding(): bool
    {
        return $this->onboarding_completed_at === null;
    }
}
