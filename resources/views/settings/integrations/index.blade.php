<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Integrations</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.integrations.update') }}" class="space-y-6">
            @csrf @method('PUT')
            @foreach ($integrations as $key => $integration)
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <h3 class="mb-3 font-semibold capitalize">{{ str_replace('_', ' ', $key) }}</h3>
                    <label class="mb-3 flex items-center gap-2">
                        <input type="checkbox" name="integrations[{{ $key }}][enabled]" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('integrations.'.$key.'.enabled', $integration['enabled'] ?? false))>
                        <span class="text-sm">Enabled</span>
                    </label>
                    <x-input-label for="integrations_{{ $key }}_api_key" value="API Key" />
                    <x-text-input id="integrations_{{ $key }}_api_key" name="integrations[{{ $key }}][api_key]" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('integrations.'.$key.'.api_key', $integration['api_key'] ?? '')" />
                </div>
            @endforeach
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Integrations</x-primary-button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
