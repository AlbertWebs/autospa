<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Vehicle</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('vehicles._form', ['vehicle' => $vehicle])
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
