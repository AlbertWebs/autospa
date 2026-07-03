<x-ui.form-page
    eyebrow="Staff"
    title="Add Employee"
    subtitle="Register a new employee record in the system."
    panel-title="Employee Details"
    panel-icon="badge"
    :action="route('employees.store')"
    submit-label="Create Employee"
    :cancel-url="route('employees.index')"
>
    @include('employees._form')
</x-ui.form-page>
