<x-ui.index-page
    eyebrow="Sales"
    title="Promotions"
    subtitle="Create promo codes to attract and retain customers."
    :create-route="route('promotions.create')"
    create-label="Add Promotion"
>
    <x-ui.data-table
        :paginator="$promotions"
        :empty="$promotions->isEmpty()"
        empty-title="No promotions yet"
        empty-description="Create promo codes to attract customers."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Code</x-ui.th>
            <x-ui.th>Value</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($promotions as $promotion)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $promotion->name }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $promotion->code }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td>{{ $promotion->type === 'percentage' ? $promotion->value.'%' : number_format($promotion->value, 2) }}</x-ui.td>
                <x-ui.td>
                    @if ($promotion->is_active)
                        <x-ui.badge color="green">Active</x-ui.badge>
                    @else
                        <x-ui.badge color="slate">Inactive</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('promotions.show', $promotion)"
                        :edit="route('promotions.edit', $promotion)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
