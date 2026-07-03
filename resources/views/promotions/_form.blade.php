@php $promotion = $promotion ?? null; @endphp

<x-ui.form-section title="Promotion Details" description="Discount code, value, schedule, and validity.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Promotion Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $promotion->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Promo Code" for="code" name="code" :required="true">
            <x-ui.input id="code" name="code" :value="old('code', $promotion->code ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Type" for="type" name="type" :required="true">
            <x-ui.select id="type" name="type" required>
                @foreach (['percentage', 'fixed'] as $type)
                    <option value="{{ $type }}" @selected(old('type', $promotion->type ?? 'percentage') == $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Value" for="value" name="value" :required="true">
            <x-ui.input id="value" name="value" type="number" step="0.01" :value="old('value', $promotion->value ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Starts At" for="starts_at" name="starts_at">
            <x-ui.input id="starts_at" name="starts_at" type="datetime-local" :value="old('starts_at', isset($promotion->starts_at) ? $promotion->starts_at->format('Y-m-d\TH:i') : '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Ends At" for="ends_at" name="ends_at">
            <x-ui.input id="ends_at" name="ends_at" type="datetime-local" :value="old('ends_at', isset($promotion->ends_at) ? $promotion->ends_at->format('Y-m-d\TH:i') : '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Description" for="description" name="description" :col-span="2">
            <x-ui.textarea id="description" name="description" rows="3">{{ old('description', $promotion->description ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $promotion->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
