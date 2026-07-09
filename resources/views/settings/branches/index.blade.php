<x-ui.index-page
    eyebrow="Settings"
    title="Branches"
    subtitle="Manage branch locations and their contact details."
    :create-route="route('settings.branches.create')"
    create-label="Add Branch"
>
    <x-ui.data-table
        :paginator="$branches"
        :empty="$branches->isEmpty()"
        empty-title="No branches yet"
        empty-description="Add your first branch location."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Code</x-ui.th>
            <x-ui.th>Phone</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($branches as $branch)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$branches" />
                <x-ui.td primary>{{ $branch->name }}</x-ui.td>
                <x-ui.td mono muted>{{ $branch->code }}</x-ui.td>
                <x-ui.td>{{ $branch->phone ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    @if ($branch->is_active)
                        <x-ui.badge color="green">Active</x-ui.badge>
                    @else
                        <x-ui.badge color="slate">Inactive</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('settings.branches.show', $branch)"
                        :edit="route('settings.branches.edit', $branch)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
