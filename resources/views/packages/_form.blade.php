@php $package = $package ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Package Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $package->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="price" value="Price" />
        <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('price', $package->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" />
    </div>
    <div>
        <x-input-label for="duration_minutes" value="Duration (minutes)" />
        <x-text-input id="duration_minutes" name="duration_minutes" type="number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('duration_minutes', $package->duration_minutes ?? '')" />
        <x-input-error :messages="$errors->get('duration_minutes')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $package->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label value="Included Services" />
        <div class="mt-2 flex flex-wrap gap-3">
            @foreach ($services as $service)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <input type="checkbox" name="services[]" value="{{ $service->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(in_array($service->id, old('services', $package?->services->pluck('id')->toArray() ?? [])))>
                    <span class="text-sm">{{ $service->name }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('services')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $package->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>
