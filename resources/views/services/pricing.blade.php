<x-ui.index-page
    eyebrow="Services"
    title="Service Pricing"
    subtitle="Current pricing for all active services."
>
    <x-ui.data-table
        :empty="$services->isEmpty()"
        :count="$services->count()"
        empty-title="No services"
        empty-description="Add services to view pricing."
    >
        <x-slot name="header">
            <x-ui.th>Service</x-ui.th>
            <x-ui.th>Category</x-ui.th>
            <x-ui.th>Price</x-ui.th>
            <x-ui.th>Duration</x-ui.th>
        </x-slot>

        @foreach ($services as $service)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $service->name }}</x-ui.td>
                <x-ui.td muted>{{ $service->category?->name ?? '—' }}</x-ui.td>
                <x-ui.td primary>{{ number_format($service->price, 2) }}</x-ui.td>
                <x-ui.td>{{ $service->duration_minutes }} min</x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
