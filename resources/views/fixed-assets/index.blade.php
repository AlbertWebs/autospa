<x-ui.index-page
    eyebrow="Inventory"
    title="Fixed Assets"
    subtitle="Record and track company-owned equipment, furniture, vehicles, and other fixed assets."
    :create-route="route('fixed-assets.create')"
    create-label="Add Asset"
>
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat-card
            label="Active Asset Value"
            :value="'KES ' . number_format($activeValue, 0)"
            icon="account_balance"
            variant="revenue"
        />
        <x-ui.stat-card
            label="Total Assets"
            :value="number_format($assets->total())"
            icon="inventory_2"
        />
    </div>

    <x-ui.data-table
        :paginator="$assets"
        :empty="$assets->isEmpty()"
        empty-title="No fixed assets yet"
        empty-description="Record company equipment, furniture, and other owned assets."
    >
        <x-slot name="header">
            <x-ui.th>Tag</x-ui.th>
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Category</x-ui.th>
            <x-ui.th>Location</x-ui.th>
            <x-ui.th align="right">Cost</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($assets as $asset)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$assets" />
                <x-ui.td mono muted>{{ $asset->asset_tag }}</x-ui.td>
                <x-ui.td primary>{{ $asset->name }}</x-ui.td>
                <x-ui.td>{{ $asset->category?->label() ?? '—' }}</x-ui.td>
                <x-ui.td muted>{{ $asset->location ?? '—' }}</x-ui.td>
                <x-ui.td align="right" mono>KES {{ number_format($asset->purchase_cost, 0) }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge :color="$asset->status?->badgeColor() ?? 'slate'">
                        {{ $asset->status?->label() ?? 'Unknown' }}
                    </x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('fixed-assets.show', $asset)"
                        :edit="route('fixed-assets.edit', $asset)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
