@php $promotion = $promotion ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Promotion Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $promotion->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="code" value="Promo Code" />
        <x-text-input id="code" name="code" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('code', $promotion->code ?? '')" required />
        <x-input-error :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach (['percentage', 'fixed'] as $type)
                <option value="{{ $type }}" @selected(old('type', $promotion->type ?? 'percentage') == $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" />
    </div>
    <div>
        <x-input-label for="value" value="Value" />
        <x-text-input id="value" name="value" type="number" step="0.01" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('value', $promotion->value ?? '')" required />
        <x-input-error :messages="$errors->get('value')" />
    </div>
    <div>
        <x-input-label for="starts_at" value="Starts At" />
        <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('starts_at', isset($promotion->starts_at) ? $promotion->starts_at->format('Y-m-d\TH:i') : '')" />
        <x-input-error :messages="$errors->get('starts_at')" />
    </div>
    <div>
        <x-input-label for="ends_at" value="Ends At" />
        <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('ends_at', isset($promotion->ends_at) ? $promotion->ends_at->format('Y-m-d\TH:i') : '')" />
        <x-input-error :messages="$errors->get('ends_at')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $promotion->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $promotion->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>
