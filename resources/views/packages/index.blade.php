<x-ui.index-page
    eyebrow="Services"
    title="Packages"
    subtitle="Bundle multiple services into discounted packages."
    :create-route="route('packages.create')"
    create-label="Add Package"
>
    <x-ui.data-table
        :paginator="$packages"
        :empty="$packages->isEmpty()"
        empty-title="No packages yet"
        empty-description="Bundle services into packages."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Price</x-ui.th>
            <x-ui.th>Services</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($packages as $package)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $package->name }}</x-ui.td>
                <x-ui.td>{{ number_format($package->price, 2) }}</x-ui.td>
                <x-ui.td muted>{{ $package->services?->count() ?? 0 }} services</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('packages.show', $package)"
                        :edit="route('packages.edit', $package)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
