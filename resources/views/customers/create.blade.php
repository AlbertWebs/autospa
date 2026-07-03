<x-ui.form-page
    eyebrow="Customers"
    title="Add Customer"
    subtitle="Register a new customer in the system."
    panel-title="Customer Details"
    panel-icon="person"
    :action="route('customers.store')"
    submit-label="Create Customer"
    :cancel-url="route('customers.index')"
>
    @include('customers._form')
</x-ui.form-page>
