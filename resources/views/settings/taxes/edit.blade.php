<x-ui.form-page
    eyebrow="Settings"
    title="Edit Tax"
    subtitle="Update tax rate and application rules."
    panel-title="Tax Details"
    panel-icon="percent"
    :action="route('settings.taxes.update', $tax)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('settings.taxes.index')"
>
    @include('settings.taxes._form', ['tax' => $tax])
</x-ui.form-page>
