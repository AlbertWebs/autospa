@php $package = $package ?? null; @endphp

<x-ui.form-section title="Package Details" description="Pricing, duration, included services, and description.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Package Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $package->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Price" for="price" name="price" :required="true">
            <x-ui.input id="price" name="price" type="number" step="0.01" :value="old('price', $package->price ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Duration (minutes)" for="duration_minutes" name="duration_minutes">
            <x-ui.input id="duration_minutes" name="duration_minutes" type="number" :value="old('duration_minutes', $package->duration_minutes ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Description" for="description" name="description" :col-span="2">
            <x-ui.textarea id="description" name="description" rows="3">{{ old('description', $package->description ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Included Services" name="services" :col-span="2">
            <div class="asp-checkbox-group">
                @foreach ($services as $service)
                    <x-ui.checkbox-card name="services[]" :value="$service->id" :checked="in_array($service->id, old('services', $package?->services->pluck('id')->toArray() ?? []))">
                        {{ $service->name }}
                    </x-ui.checkbox-card>
                @endforeach
            </div>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $package->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
