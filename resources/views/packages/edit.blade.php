<x-ui.form-page
    eyebrow="Services"
    title="Edit Package"
    subtitle="Update package services, pricing, and availability."
    panel-title="Package Details"
    panel-icon="redeem"
    :action="route('packages.update', $package)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('packages.index')"
>
    @include('packages._form', ['package' => $package])
</x-ui.form-page>
