<x-ui.form-page
    eyebrow="Settings"
    title="Add Tax"
    subtitle="Define a new tax rate for transactions."
    panel-title="Tax Details"
    panel-icon="percent"
    :action="route('settings.taxes.store')"
    submit-label="Create Tax"
    :cancel-url="route('settings.taxes.index')"
>
    @include('settings.taxes._form')
</x-ui.form-page>
