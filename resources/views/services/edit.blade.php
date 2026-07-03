<x-ui.form-page
    eyebrow="Services"
    title="Edit Service"
    subtitle="Update service pricing, duration, and details."
    panel-title="Service Details"
    panel-icon="auto_awesome"
    :action="route('services.update', $service)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('services.index')"
>
    @include('services._form', ['service' => $service])
</x-ui.form-page>
