@php $asset = $asset ?? null; @endphp

<x-ui.form-section title="Asset Details" description="Record company-owned equipment, furniture, vehicles, and other fixed assets.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Asset Tag" for="asset_tag">
            @if ($asset)
                <x-ui.input id="asset_tag" :value="$asset->asset_tag" readonly disabled />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Auto-generated and cannot be changed.</p>
            @else
                <x-ui.input id="asset_tag" :value="$nextAssetTag ?? ''" readonly disabled />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Assigned automatically when you save.</p>
            @endif
        </x-ui.form-field>

        <x-ui.form-field label="Asset Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $asset->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Category" for="category" name="category" :required="true">
            <x-ui.select id="category" name="category" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->value }}" @selected(old('category', $asset->category?->value ?? 'equipment') === $category->value)>
                        {{ $category->label() }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Status" for="status" name="status" :required="true">
            <x-ui.select id="status" name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $asset->status?->value ?? 'active') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Location" for="location" name="location">
            <x-ui.input id="location" name="location" :value="old('location', $asset->location ?? '')" placeholder="e.g. Bay 1, Office, Storage" />
        </x-ui.form-field>

        <x-ui.form-field label="Purchase Date" for="purchase_date" name="purchase_date">
            <x-ui.input id="purchase_date" name="purchase_date" type="date" :value="old('purchase_date', optional($asset?->purchase_date)->toDateString())" />
        </x-ui.form-field>

        <x-ui.form-field label="Purchase Cost (KES)" for="purchase_cost" name="purchase_cost">
            <x-ui.input id="purchase_cost" name="purchase_cost" type="number" step="0.01" min="0" :value="old('purchase_cost', $asset->purchase_cost ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Supplier" for="supplier_id" name="supplier_id">
            <x-ui.select id="supplier_id" name="supplier_id">
                <option value="">Select supplier…</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $asset->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Assigned To" for="assigned_employee_id" name="assigned_employee_id">
            <x-ui.select id="assigned_employee_id" name="assigned_employee_id">
                <option value="">Unassigned</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('assigned_employee_id', $asset->assigned_employee_id ?? '') == $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Description / Notes" for="description" name="description" :col-span="2">
            <x-ui.textarea id="description" name="description" rows="3">{{ old('description', $asset->description ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $asset->is_active ?? true)">Active record</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
