@php $product = $product ?? null; @endphp

<x-ui.form-section title="Product Information" description="SKU, pricing, stock levels, and supplier details.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Supplier" for="supplier_id" name="supplier_id">
            <x-ui.select id="supplier_id" name="supplier_id">
                <option value="">Select supplier…</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="SKU" for="sku" name="sku" :required="true">
            <x-ui.input id="sku" name="sku" :value="old('sku', $product->sku ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Product Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $product->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Unit" for="unit" name="unit">
            <x-ui.input id="unit" name="unit" :value="old('unit', $product->unit ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Cost Price" for="cost_price" name="cost_price">
            <x-ui.input id="cost_price" name="cost_price" type="number" step="0.01" :value="old('cost_price', $product->cost_price ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Selling Price" for="selling_price" name="selling_price">
            <x-ui.input id="selling_price" name="selling_price" type="number" step="0.01" :value="old('selling_price', $product->selling_price ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Quantity on Hand" for="quantity_on_hand" name="quantity_on_hand">
            <x-ui.input id="quantity_on_hand" name="quantity_on_hand" type="number" :value="old('quantity_on_hand', $product->quantity_on_hand ?? 0)" />
        </x-ui.form-field>

        <x-ui.form-field label="Minimum Level" for="minimum_level" name="minimum_level">
            <x-ui.input id="minimum_level" name="minimum_level" type="number" :value="old('minimum_level', $product->minimum_level ?? 0)" />
        </x-ui.form-field>

        <x-ui.form-field label="Description" for="description" name="description" :col-span="2">
            <x-ui.textarea id="description" name="description" rows="3">{{ old('description', $product->description ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $product->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
