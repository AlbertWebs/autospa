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
    <x-ui.form-section title="Permissions" description="Select the permissions granted to users with this role.">
        <x-ui.form-field name="permissions" :col-span="2">
            <div class="asp-checkbox-group">
                @foreach ($permissions as $permission)
                    <x-ui.checkbox-card
                        name="permissions[]"
                        :value="$permission->id"
                        :checked="in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray()))"
                    >
                        {{ $permission->name }}
                    </x-ui.checkbox-card>
                @endforeach
            </div>
        </x-ui.form-field>
    </x-ui.form-section>
</x-ui.form-page>
