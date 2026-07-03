@php $purchaseOrder = $purchaseOrder ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="supplier_id" value="Supplier" />
        <select id="supplier_id" name="supplier_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            <option value="">Select supplier…</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseOrder->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('supplier_id')" />
    </div>
    <div>
        <x-input-label for="reference" value="Reference" />
        <x-text-input id="reference" name="reference" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('reference', $purchaseOrder->reference ?? '')" />
        <x-input-error :messages="$errors->get('reference')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach (['draft', 'ordered', 'received', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $purchaseOrder->status ?? 'draft') == $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('notes', $purchaseOrder->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>
