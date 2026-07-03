@php $branch = $branch ?? null; @endphp

<x-ui.form-section title="Branch Information" description="Branch name, code, contact details, and address.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Branch Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $branch->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Branch Code" for="code" name="code" :required="true">
            <x-ui.input id="code" name="code" :value="old('code', $branch->code ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Address" for="address" name="address" :col-span="2">
            <x-ui.textarea id="address" name="address" rows="2">{{ old('address', $branch->address ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone">
            <x-ui.input id="phone" name="phone" :value="old('phone', $branch->phone ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $branch->email ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $branch->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
