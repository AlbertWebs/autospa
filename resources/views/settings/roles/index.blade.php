<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Roles</h1></x-slot>

    @include('partials.crud.index-header', ['createRoute' => null, 'createLabel' => 'Add Role', 'title' => 'Roles'])

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Permissions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $role->name }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $role->permissions->count() }} permissions</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('settings.roles.edit', $role) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->isEmpty())
            <x-ui.empty-state title="No roles yet" description="Roles define what team members can access." />
        @endif
    </x-ui.card>
</x-layouts.app>
