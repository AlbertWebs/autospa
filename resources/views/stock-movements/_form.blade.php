@php $movement = $movement ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="product_id" value="Product" />
        <select id="product_id" name="product_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            <option value="">Select product…</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected(old('product_id', $movement->product_id ?? '') == $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('product_id')" />
    </div>
    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach (['in', 'out', 'adjustment'] as $type)
                <option value="{{ $type }}" @selected(old('type', $movement->type ?? 'in') == $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" />
    </div>
    <div>
        <x-input-label for="quantity" value="Quantity" />
        <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('quantity', $movement->quantity ?? '')" required />
        <x-input-error :messages="$errors->get('quantity')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('notes', $movement->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>
