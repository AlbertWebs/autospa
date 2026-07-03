<x-ui.form-page
    eyebrow="Staff"
    title="Edit Employee"
    subtitle="Update employee record details."
    panel-title="Employee Details"
    panel-icon="badge"
    :action="route('employees.update', $employee)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('employees.index')"
>
    @include('employees._form', ['employee' => $employee])
</x-ui.form-page>
