<x-ui.form-page
    eyebrow="Customers"
    title="Edit Customer"
    subtitle="Update customer contact and profile details."
    panel-title="Customer Details"
    panel-icon="person"
    :action="route('customers.update', $customer)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('customers.index')"
>
    @include('customers._form', ['customer' => $customer])
</x-ui.form-page>
