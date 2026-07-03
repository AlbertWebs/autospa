<x-ui.form-page
    eyebrow="Settings"
    title="Add Branch"
    subtitle="Register a new business location or branch."
    panel-title="Branch Details"
    panel-icon="store"
    :action="route('settings.branches.store')"
    submit-label="Create Branch"
    :cancel-url="route('settings.branches.index')"
>
    @include('settings.branches._form')
</x-ui.form-page>
