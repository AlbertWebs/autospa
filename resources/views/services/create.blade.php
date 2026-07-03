<x-ui.form-page
    eyebrow="Services"
    title="Add Service"
    subtitle="Define a new service offering for customers."
    panel-title="Service Details"
    panel-icon="auto_awesome"
    :action="route('services.store')"
    submit-label="Create Service"
    :cancel-url="route('services.index')"
>
    @include('services._form')
</x-ui.form-page>
