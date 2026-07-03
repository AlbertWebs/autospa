<x-ui.form-page
    eyebrow="Settings"
    title="Edit User"
    subtitle="Update user account details and role assignment."
    panel-title="User Details"
    panel-icon="group"
    :action="route('settings.users.update', $user)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('settings.users.index')"
>
    @include('settings.users._form', ['user' => $user])
</x-ui.form-page>
