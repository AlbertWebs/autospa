<x-ui.index-page
    eyebrow="Customers"
    title="Customers"
    subtitle="Manage customer profiles, contact details, and service history."
    :create-route="route('customers.create')"
    create-label="Add Customer"
>
    <x-ui.data-table
        :paginator="$customers"
        :empty="$customers->isEmpty()"
        empty-title="No customers yet"
        empty-description="Add your first customer to start booking services."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Phone</x-ui.th>
            <x-ui.th>Email</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($customers as $customer)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$customers" />
                <x-ui.td primary>{{ $customer->full_name }}</x-ui.td>
                <x-ui.td>{{ $customer->phone }}</x-ui.td>
                <x-ui.td muted>{{ $customer->email ?? 'N/A' }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('customers.show', $customer)"
                        :edit="route('customers.edit', $customer)"
                        :delete="route('customers.destroy', $customer)"
                        :delete-visible="auth()->user()->can('delete', $customer)"
                        delete-confirm="Delete this customer permanently?"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
