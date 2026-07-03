@php $product = $product ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="supplier_id" value="Supplier" />
        <select id="supplier_id" name="supplier_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">Select supplier…</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('supplier_id')" />
    </div>
    <div>
        <x-input-label for="sku" value="SKU" />
        <x-text-input id="sku" name="sku" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('sku', $product->sku ?? '')" required />
        <x-input-error :messages="$errors->get('sku')" />
    </div>
    <div>
        <x-input-label for="name" value="Product Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $product->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="unit" value="Unit" />
        <x-text-input id="unit" name="unit" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('unit', $product->unit ?? '')" />
        <x-input-error :messages="$errors->get('unit')" />
    </div>
    <div>
        <x-input-label for="cost_price" value="Cost Price" />
        <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('cost_price', $product->cost_price ?? '')" />
        <x-input-error :messages="$errors->get('cost_price')" />
    </div>
    <div>
        <x-input-label for="selling_price" value="Selling Price" />
        <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('selling_price', $product->selling_price ?? '')" />
        <x-input-error :messages="$errors->get('selling_price')" />
    </div>
    <div>
        <x-input-label for="quantity_on_hand" value="Quantity on Hand" />
        <x-text-input id="quantity_on_hand" name="quantity_on_hand" type="number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('quantity_on_hand', $product->quantity_on_hand ?? 0)" />
        <x-input-error :messages="$errors->get('quantity_on_hand')" />
    </div>
    <div>
        <x-input-label for="minimum_level" value="Minimum Level" />
        <x-text-input id="minimum_level" name="minimum_level" type="number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('minimum_level', $product->minimum_level ?? 0)" />
        <x-input-error :messages="$errors->get('minimum_level')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $product->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $product->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>
