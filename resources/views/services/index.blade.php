<x-ui.index-page
    eyebrow="Services"
    title="Services"
    subtitle="Manage individual services, pricing, and durations."
    :create-route="route('services.create')"
    create-label="Add Service"
>
    <x-ui.data-table
        :paginator="$services"
        :empty="$services->isEmpty()"
        empty-title="No services yet"
        empty-description="Add services your spa offers."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Category</x-ui.th>
            <x-ui.th>Price</x-ui.th>
            <x-ui.th>Duration</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($services as $service)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $service->name }}</x-ui.td>
                <x-ui.td muted>{{ $service->category?->name ?? '—' }}</x-ui.td>
                <x-ui.td>{{ number_format($service->price, 2) }}</x-ui.td>
                <x-ui.td>{{ $service->duration_minutes }} min</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('services.show', $service)"
                        :edit="route('services.edit', $service)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
