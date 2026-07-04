<x-ui.form-page
    eyebrow="Settings"
    :title="$role->name"
    subtitle="Assign permissions for this role."
    panel-title="Role Permissions"
    panel-icon="admin_panel_settings"
    :action="route('settings.roles.update', $role)"
    method="PUT"
    submit-label="Save Permissions"
    :cancel-url="route('settings.roles.index')"
>
    @php
        $permissionGroups = $permissions->groupBy(fn ($permission) => $permission->group ?? 'other');
    @endphp

    <x-ui.form-section title="Permissions" description="Select the permissions granted to users with this role.">
        <x-ui.form-field name="permissions" :col-span="2">
            <div class="space-y-5">
                @foreach ($permissionGroups as $group => $groupPermissions)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ \Illuminate\Support\Str::headline($group) }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $groupPermissions->count() }} permission{{ $groupPermissions->count() === 1 ? '' : 's' }}
                                </p>
                            </div>
                        </div>

                        <div class="asp-checkbox-group">
                            @foreach ($groupPermissions as $permission)
                                <x-ui.checkbox-card
                                    name="permissions[]"
                                    :value="$permission->id"
                                    :checked="in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray()))"
                                >
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $permission->name }}</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $permission->slug }}</span>
                                </x-ui.checkbox-card>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.form-field>
    </x-ui.form-section>
</x-ui.form-page>
