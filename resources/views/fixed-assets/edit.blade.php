<x-ui.form-page
    eyebrow="Inventory"
    title="Edit Fixed Asset"
    subtitle="Update asset details, location, assignment, or status."
    panel-title="Asset Details"
    panel-icon="account_balance"
    :action="route('fixed-assets.update', $asset)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('fixed-assets.index')"
>
    @include('fixed-assets._form', ['asset' => $asset])
</x-ui.form-page>
