<x-ui.index-page
    eyebrow="Staff"
    title="Employees"
    subtitle="Manage your team members, roles, and employment records."
    :create-route="route('employees.create')"
    create-label="Add Employee"
>
    <x-ui.data-table
        :paginator="$employees"
        :empty="$employees->isEmpty()"
        empty-title="No employees yet"
        empty-description="Add staff members to manage your team."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Employee #</x-ui.th>
            <x-ui.th>Type</x-ui.th>
            <x-ui.th>Phone</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($employees as $employee)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $employee->full_name }}</x-ui.td>
                <x-ui.td mono>{{ $employee->employee_number ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ $employee->typeLabel() }}</x-ui.td>
                <x-ui.td muted>{{ $employee->phone ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    @if ($employee->is_active)
                        <x-ui.badge color="green">Active</x-ui.badge>
                    @else
                        <x-ui.badge color="slate">Inactive</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('employees.show', $employee)"
                        :edit="route('employees.edit', $employee)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
