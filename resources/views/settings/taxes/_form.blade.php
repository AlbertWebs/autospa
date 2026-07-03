@php $tax = $tax ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Tax Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $tax->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('code', $tax->code ?? '')" required />
        <x-input-error :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="rate" value="Rate (%)" />
        <x-text-input id="rate" name="rate" type="number" step="0.01" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('rate', $tax->rate ?? '')" required />
        <x-input-error :messages="$errors->get('rate')" />
    </div>
    <div class="sm:col-span-2 flex flex-wrap gap-6">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $tax->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_default" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_default', $tax->is_default ?? false))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Default tax</span>
        </label>
    </div>
</div>
