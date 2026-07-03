<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Business Hours</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.business-hours.update') }}" class="space-y-6">
            @csrf @method('PUT')
            @foreach ($businessHours as $day => $hours)
                <div class="grid gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-4 dark:border-slate-700">
                    <div class="font-medium capitalize">{{ $day }}</div>
                    <div>
                        <x-input-label for="{{ $day }}_open" value="Opens" />
                        <x-text-input id="{{ $day }}_open" name="hours[{{ $day }}][open]" type="time" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('hours.'.$day.'.open', $hours['open'] ?? '')" />
                    </div>
                    <div>
                        <x-input-label for="{{ $day }}_close" value="Closes" />
                        <x-text-input id="{{ $day }}_close" name="hours[{{ $day }}][close]" type="time" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('hours.'.$day.'.close', $hours['close'] ?? '')" />
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 pb-2">
                            <input type="checkbox" name="hours[{{ $day }}][closed]" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('hours.'.$day.'.closed', $hours['closed'] ?? false))>
                            <span class="text-sm">Closed</span>
                        </label>
                    </div>
                </div>
            @endforeach
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Business Hours</x-primary-button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
