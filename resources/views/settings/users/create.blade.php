<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Add User</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.users.store') }}" class="space-y-6">
            @csrf
            @include('settings.users._form')
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Create User</x-primary-button>
                <a href="{{ route('settings.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
