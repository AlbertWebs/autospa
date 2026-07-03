<x-ui.form-page
    eyebrow="Settings"
    title="Add User"
    subtitle="Create a new system user account."
    panel-title="User Details"
    panel-icon="group"
    :action="route('settings.users.store')"
    submit-label="Create User"
    :cancel-url="route('settings.users.index')"
>
    @include('settings.users._form')
</x-ui.form-page>
