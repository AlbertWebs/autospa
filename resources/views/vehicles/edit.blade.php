<x-ui.form-page
    eyebrow="Vehicles"
    title="Edit Vehicle"
    subtitle="Update vehicle details and ownership information."
    panel-title="Vehicle Details"
    panel-icon="directions_car"
    :action="route('vehicles.update', $vehicle)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('vehicles.index')"
>
    @include('vehicles._form', ['vehicle' => $vehicle])
</x-ui.form-page>
