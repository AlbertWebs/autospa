<x-ui.index-page
    eyebrow="Settings"
    title="Taxes"
    subtitle="Configure tax rates for invoicing and point of sale."
    :create-route="route('settings.taxes.create')"
    create-label="Add Tax"
>
    <x-ui.data-table
        :paginator="$taxes"
        :empty="$taxes->isEmpty()"
        empty-title="No taxes configured"
        empty-description="Add tax rates for invoicing and POS."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Code</x-ui.th>
            <x-ui.th>Rate</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($taxes as $tax)
            <tr class="asp-table-row">
                <x-ui.td primary>
                    {{ $tax->name }}
                    @if ($tax->is_default)
                        <x-ui.badge color="indigo">Default</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td mono muted>{{ $tax->code }}</x-ui.td>
                <x-ui.td>{{ $tax->rate }}%</x-ui.td>
                <x-ui.td>
                    @if ($tax->is_active)
                        <x-ui.badge color="green">Active</x-ui.badge>
                    @else
                        <x-ui.badge color="slate">Inactive</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('settings.taxes.show', $tax)"
                        :edit="route('settings.taxes.edit', $tax)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
