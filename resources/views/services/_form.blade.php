@php $service = $service ?? null; @endphp

<x-ui.form-section title="Service Details" description="Pricing, duration, and category for this service.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Category" for="service_category_id" name="service_category_id" :required="true" :col-span="2">
            <x-ui.select id="service_category_id" name="service_category_id" required>
                <option value="">Select category…</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('service_category_id', $service->service_category_id ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Service Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $service->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Price" for="price" name="price" :required="true">
            <x-ui.input id="price" name="price" type="number" step="0.01" :value="old('price', $service->price ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Duration (minutes)" for="duration_minutes" name="duration_minutes" :required="true">
            <x-ui.input id="duration_minutes" name="duration_minutes" type="number" :value="old('duration_minutes', $service->duration_minutes ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Description" for="description" name="description" :col-span="2">
            <x-ui.textarea id="description" name="description" rows="3">{{ old('description', $service->description ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $service->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
