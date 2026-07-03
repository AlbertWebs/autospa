<x-ui.index-page
    eyebrow="Services"
    title="Service Categories"
    subtitle="Organize services into categories for menus and reporting."
    :create-route="route('services.categories.create')"
    create-label="Add Category"
>
    <x-ui.data-table
        :paginator="$categories"
        :empty="$categories->isEmpty()"
        empty-title="No categories yet"
        empty-description="Organize your services into categories."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Sort</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($categories as $category)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $category->name }}</x-ui.td>
                <x-ui.td muted>{{ $category->sort_order }}</x-ui.td>
                <x-ui.td>
                    @if ($category->is_active)
                        <x-ui.badge color="green">Active</x-ui.badge>
                    @else
                        <x-ui.badge color="slate">Inactive</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('services.categories.show', $category)"
                        :edit="route('services.categories.edit', $category)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
