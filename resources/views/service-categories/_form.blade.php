@php $category = $category ?? null; @endphp

<x-ui.form-section title="Category Information" description="Name, sort order, and description for this service category.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $category->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Sort Order" for="sort_order" name="sort_order">
            <x-ui.input id="sort_order" name="sort_order" type="number" :value="old('sort_order', $category->sort_order ?? 0)" />
        </x-ui.form-field>

        <x-ui.form-field label="Description" for="description" name="description" :col-span="2">
            <x-ui.textarea id="description" name="description" rows="3">{{ old('description', $category->description ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $category->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
