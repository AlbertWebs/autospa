<x-ui.index-page
    eyebrow="Settings"
    title="Roles"
    subtitle="Define what team members can access in the system."
>
    <x-ui.data-table
        :empty="$roles->isEmpty()"
        :count="$roles->count()"
        empty-title="No roles yet"
        empty-description="Roles define what team members can access."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Permissions</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($roles as $role)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $role->name }}</x-ui.td>
                <x-ui.td muted>{{ $role->permissions_count }} permissions</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :edit="route('settings.roles.edit', $role)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
