<x-ui.form-page
    eyebrow="Settings"
    title="Edit Branch"
    subtitle="Update branch address, contact, and operating details."
    panel-title="Branch Details"
    panel-icon="store"
    :action="route('settings.branches.update', $branch)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('settings.branches.index')"
>
    @include('settings.branches._form', ['branch' => $branch])
</x-ui.form-page>
