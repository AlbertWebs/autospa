@php $vehicle = $vehicle ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            <option value="">Select customer…</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $vehicle->customer_id ?? '') == $customer->id)>{{ $customer->full_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" />
    </div>
    <div>
        <x-input-label for="registration_number" value="Registration Number" />
        <x-text-input id="registration_number" name="registration_number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('registration_number', $vehicle->registration_number ?? '')" required />
        <x-input-error :messages="$errors->get('registration_number')" />
    </div>
    <div>
        <x-input-label for="make" value="Make" />
        <x-text-input id="make" name="make" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('make', $vehicle->make ?? '')" required />
        <x-input-error :messages="$errors->get('make')" />
    </div>
    <div>
        <x-input-label for="model" value="Model" />
        <x-text-input id="model" name="model" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('model', $vehicle->model ?? '')" required />
        <x-input-error :messages="$errors->get('model')" />
    </div>
    <div>
        <x-input-label for="year" value="Year" />
        <x-text-input id="year" name="year" type="number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('year', $vehicle->year ?? '')" />
        <x-input-error :messages="$errors->get('year')" />
    </div>
    <div>
        <x-input-label for="color" value="Color" />
        <x-text-input id="color" name="color" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('color', $vehicle->color ?? '')" />
        <x-input-error :messages="$errors->get('color')" />
    </div>
    <div>
        <x-input-label for="vin" value="VIN" />
        <x-text-input id="vin" name="vin" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('vin', $vehicle->vin ?? '')" />
        <x-input-error :messages="$errors->get('vin')" />
    </div>
    <div>
        <x-input-label for="mileage" value="Mileage" />
        <x-text-input id="mileage" name="mileage" type="number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('mileage', $vehicle->mileage ?? '')" />
        <x-input-error :messages="$errors->get('mileage')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            @foreach (['active', 'in_service', 'ready', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $vehicle->status ?? 'active') == $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
</div>
