@php $movement = $movement ?? null; @endphp

<x-ui.form-section title="Stock Movement" description="Record inventory in, out, or adjustment for a product.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Product" for="product_id" name="product_id" :required="true" :col-span="2">
            <x-ui.select id="product_id" name="product_id" required>
                <option value="">Select product…</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id', $movement->product_id ?? '') == $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Type" for="type" name="type" :required="true">
            <x-ui.select id="type" name="type" required>
                @foreach (['in', 'out', 'adjustment'] as $type)
                    <option value="{{ $type }}" @selected(old('type', $movement->type ?? 'in') == $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Quantity" for="quantity" name="quantity" :required="true">
            <x-ui.input id="quantity" name="quantity" type="number" :value="old('quantity', $movement->quantity ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Notes" for="notes" name="notes" :col-span="2">
            <x-ui.textarea id="notes" name="notes" rows="3">{{ old('notes', $movement->notes ?? '') }}</x-ui.textarea>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
