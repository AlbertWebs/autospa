@php $purchaseOrder = $purchaseOrder ?? null; @endphp

<x-ui.form-section title="Purchase Order" description="Supplier, reference, status, and notes for this order.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Supplier" for="supplier_id" name="supplier_id" :required="true">
            <x-ui.select id="supplier_id" name="supplier_id" required>
                <option value="">Select supplier…</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseOrder->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Reference" for="reference" name="reference">
            <x-ui.input id="reference" name="reference" :value="old('reference', $purchaseOrder->reference ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Status" for="status" name="status" :required="true">
            <x-ui.select id="status" name="status" required>
                @foreach (['draft', 'ordered', 'received', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $purchaseOrder->status ?? 'draft') == $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Notes" for="notes" name="notes" :col-span="2">
            <x-ui.textarea id="notes" name="notes" rows="3">{{ old('notes', $purchaseOrder->notes ?? '') }}</x-ui.textarea>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
