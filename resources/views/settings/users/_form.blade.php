@php
    $user = $user ?? null;
    $selectedRoleIds = collect(old('roles', $user?->roles->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $selectedRoles = $roles->whereIn('id', $selectedRoleIds);
    $selectedPermissionGroups = $selectedRoles
        ->flatMap(fn ($role) => $role->permissions)
        ->unique('id')
        ->groupBy(fn ($permission) => $permission->group ?? 'other')
        ->sortKeys();
@endphp

<x-ui.form-section title="User Account" description="Account details, branch assignment, roles, and credentials.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Full Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $user->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email" :required="true">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $user->email ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone">
            <x-ui.input id="phone" name="phone" :value="old('phone', $user->phone ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field
            label="Branch"
            for="branch_id"
            name="branch_id"
            :required="true"
            hint="Non-super-admin users operate within their assigned branch."
        >
            <x-ui.select id="branch_id" name="branch_id" required>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id ?? '') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field :label="$user ? 'New Password (leave blank to keep)' : 'Password'" for="password" name="password">
            <x-ui.input id="password" name="password" type="password" :required="!$user" />
        </x-ui.form-field>

        <x-ui.form-field label="Confirm Password" for="password_confirmation" name="password_confirmation">
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" />
        </x-ui.form-field>

        <x-ui.form-field label="Roles" name="roles" :col-span="2">
            <div class="asp-checkbox-group">
                @foreach ($roles as $role)
                    <x-ui.checkbox-card name="roles[]" :value="$role->id" :checked="in_array($role->id, old('roles', $user?->roles->pluck('id')->toArray() ?? []))">
                        <span class="font-medium text-slate-900 dark:text-white">{{ $role->name }}</span>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">
                            {{ $role->permissions_count }} permission{{ $role->permissions_count === 1 ? '' : 's' }}
                        </span>
                        @if ($role->description)
                            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $role->description }}</span>
                        @endif
                        @php
                            $roleGroups = $role->permissions->pluck('group')->filter()->unique()->values();
                        @endphp
                        @if ($roleGroups->isNotEmpty())
                            <span class="mt-2 flex flex-wrap gap-1">
                                @foreach ($roleGroups->take(3) as $group)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ \Illuminate\Support\Str::headline($group) }}
                                    </span>
                                @endforeach
                                @if ($roleGroups->count() > 3)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        +{{ $roleGroups->count() - 3 }} more
                                    </span>
                                @endif
                            </span>
                        @endif
                    </x-ui.checkbox-card>
                @endforeach
            </div>
        </x-ui.form-field>

        <x-ui.form-field
            label="Access Preview"
            :col-span="2"
            hint="The effective permissions below are derived from the currently selected roles."
        >
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                @if ($selectedRoles->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">Choose one or more roles to grant module access.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach ($selectedRoles as $role)
                            <x-ui.badge color="indigo">{{ $role->name }}</x-ui.badge>
                        @endforeach
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($selectedPermissionGroups as $group => $permissions)
                            <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ \Illuminate\Support\Str::headline($group) }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $permissions->count() }} permission{{ $permissions->count() === 1 ? '' : 's' }}
                                </p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($permissions->sortBy('name') as $permission)
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $user->is_active ?? true)">Active</x-ui.checkbox>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Inactive users keep their role assignments but can no longer sign in.</p>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
