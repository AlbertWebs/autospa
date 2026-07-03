@php $service = $service ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="service_category_id" value="Category" />
        <select id="service_category_id" name="service_category_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            <option value="">Select category…</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('service_category_id', $service->service_category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('service_category_id')" />
    </div>
    <div>
        <x-input-label for="name" value="Service Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $service->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="price" value="Price" />
        <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('price', $service->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" />
    </div>
    <div>
        <x-input-label for="duration_minutes" value="Duration (minutes)" />
        <x-text-input id="duration_minutes" name="duration_minutes" type="number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('duration_minutes', $service->duration_minutes ?? '')" required />
        <x-input-error :messages="$errors->get('duration_minutes')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $service->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $service->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>
