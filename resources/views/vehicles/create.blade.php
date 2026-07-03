<x-ui.form-page
    eyebrow="Vehicles"
    title="Add Vehicle"
    subtitle="Register a new vehicle in the system."
    panel-title="Vehicle Details"
    panel-icon="directions_car"
    :action="route('vehicles.store')"
    submit-label="Create Vehicle"
    :cancel-url="route('vehicles.index')"
>
    @include('vehicles._form')
</x-ui.form-page>
