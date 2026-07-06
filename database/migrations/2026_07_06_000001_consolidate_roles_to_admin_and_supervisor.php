<?php

use App\Enums\RoleSlug;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $supervisor = Role::query()->where('slug', RoleSlug::Manager->value)->first();

        Role::query()
            ->whereNotIn('slug', RoleSlug::values())
            ->each(function (Role $role) use ($supervisor) {
                foreach ($role->users as $user) {
                    $role->users()->detach($user->id);

                    if ($supervisor && ! $user->roles()->exists()) {
                        $user->roles()->attach($supervisor->id);
                    }
                }

                $role->permissions()->detach();
                $role->delete();
            });

        Role::query()->where('slug', RoleSlug::SuperAdmin->value)->update(['name' => 'Admin']);
        Role::query()->where('slug', RoleSlug::Manager->value)->update(['name' => 'Supervisor']);
    }

    public function down(): void
    {
        // Legacy roles are not restored.
    }
};
