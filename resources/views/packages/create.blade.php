<x-ui.form-page
    eyebrow="Services"
    title="Add Package"
    subtitle="Bundle services into a package offering."
    panel-title="Package Details"
    panel-icon="redeem"
    :action="route('packages.store')"
    submit-label="Create Package"
    :cancel-url="route('packages.index')"
>
    @include('packages._form')
</x-ui.form-page>
