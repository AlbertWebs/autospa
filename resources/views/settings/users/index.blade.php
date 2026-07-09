<x-ui.index-page
    eyebrow="Settings"
    title="Users"
    subtitle="Manage team members and their access across branches."
    :create-route="route('settings.users.create')"
    create-label="Add User"
>
    <x-ui.data-table
        :paginator="$users"
        :empty="$users->isEmpty()"
        empty-title="No users yet"
        empty-description="Add team members to manage your AutoSpa branches."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Email</x-ui.th>
            <x-ui.th>Branch</x-ui.th>
            <x-ui.th>Roles</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($users as $user)
            <tr class="asp-table-row">
                <x-ui.table-number-td :loop="$loop" :paginator="$users" />
                <x-ui.td primary>{{ $user->name }}</x-ui.td>
                <x-ui.td muted>{{ $user->email }}</x-ui.td>
                <x-ui.td>{{ $user->branch?->name ?? 'N/A' }}</x-ui.td>
                <x-ui.td wrap>
                    @foreach ($user->roles as $role)
                        <x-ui.badge color="indigo">{{ $role->name }}</x-ui.badge>
                    @endforeach
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('settings.users.show', $user)"
                        :edit="route('settings.users.edit', $user)"
                        :delete="route('settings.users.destroy', $user)"
                        :delete-visible="auth()->user()->can('delete', $user)"
                        delete-confirm="Delete this user permanently?"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
