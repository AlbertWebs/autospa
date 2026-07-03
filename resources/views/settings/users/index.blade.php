<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Users</h1></x-slot>

    @include('partials.crud.index-header', ['createRoute' => route('settings.users.create'), 'createLabel' => 'Add User', 'title' => 'Users'])

    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Roles</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $user->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $user->branch?->name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @foreach ($user->roles as $role)
                                    <x-ui.badge color="indigo">{{ $role->name }}</x-ui.badge>
                                @endforeach
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('settings.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('settings.users.edit', $user) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->isEmpty())
            <x-ui.empty-state title="No users yet" description="Add team members to manage your AutoSpa branches." />
        @endif
        @include('partials.crud.pagination', ['paginator' => $users])
    </x-ui.card>
</x-layouts.app>
