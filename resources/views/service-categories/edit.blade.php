<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Category</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('services.categories.update', $category) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('service-categories._form', ['category' => $category])
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('services.categories.show', $category) }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
