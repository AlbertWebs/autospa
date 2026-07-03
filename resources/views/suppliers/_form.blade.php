@php $supplier = $supplier ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Supplier Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $supplier->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="contact_person" value="Contact Person" />
        <x-text-input id="contact_person" name="contact_person" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('contact_person', $supplier->contact_person ?? '')" />
        <x-input-error :messages="$errors->get('contact_person')" />
    </div>
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('phone', $supplier->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('email', $supplier->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('address', $supplier->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $supplier->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>
