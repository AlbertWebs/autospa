<x-ui.index-page
    eyebrow="Settings"
    title="Payment Methods"
    subtitle="Configure how customers can pay for services."
    :create-route="route('settings.payment-methods.create')"
    create-label="Add Payment Method"
>
    <x-ui.data-table
        :paginator="$paymentMethods"
        :empty="$paymentMethods->isEmpty()"
        empty-title="No payment methods"
        empty-description="Configure how customers can pay."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Slug</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($paymentMethods as $paymentMethod)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $paymentMethod->name }}</x-ui.td>
                <x-ui.td mono muted>{{ $paymentMethod->slug }}</x-ui.td>
                <x-ui.td>
                    @if ($paymentMethod->is_active)
                        <x-ui.badge color="green">Active</x-ui.badge>
                    @else
                        <x-ui.badge color="slate">Inactive</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('settings.payment-methods.show', $paymentMethod)"
                        :edit="route('settings.payment-methods.edit', $paymentMethod)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
