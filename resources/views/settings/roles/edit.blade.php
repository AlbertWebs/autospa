<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $role->name }}</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.roles.update', $role) }}" class="space-y-6">
            @csrf @method('PUT')
            <div>
                <x-input-label value="Permissions" />
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())))>
                            <span class="text-sm">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('permissions')" />
            </div>
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Permissions</x-primary-button>
                <a href="{{ route('settings.roles.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
