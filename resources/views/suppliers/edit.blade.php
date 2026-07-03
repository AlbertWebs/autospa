<x-ui.form-page
    eyebrow="Inventory"
    title="Edit Supplier"
    subtitle="Update supplier contact and account details."
    panel-title="Supplier Details"
    panel-icon="local_shipping"
    :action="route('suppliers.update', $supplier)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('suppliers.index')"
>
    @include('suppliers._form', ['supplier' => $supplier])
</x-ui.form-page>
