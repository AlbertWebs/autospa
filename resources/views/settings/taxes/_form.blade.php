@php $tax = $tax ?? null; @endphp

<x-ui.form-section title="Tax Configuration" description="Tax name, code, rate, and default settings.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Tax Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $tax->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Code" for="code" name="code" :required="true">
            <x-ui.input id="code" name="code" :value="old('code', $tax->code ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Rate (%)" for="rate" name="rate" :required="true">
            <x-ui.input id="rate" name="rate" type="number" step="0.01" :value="old('rate', $tax->rate ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field :col-span="2">
            <div class="asp-checkbox-group">
                <x-ui.checkbox name="is_active" :checked="old('is_active', $tax->is_active ?? true)">Active</x-ui.checkbox>
                <x-ui.checkbox name="is_default" :checked="old('is_default', $tax->is_default ?? false)">Default tax</x-ui.checkbox>
            </div>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
