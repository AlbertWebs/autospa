@php $booking = $booking ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            <option value="">Select customer…</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $booking->customer_id ?? '') == $customer->id)>{{ $customer->full_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" />
    </div>
    <div>
        <x-input-label for="vehicle_id" value="Vehicle" />
        <select id="vehicle_id" name="vehicle_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">Select vehicle…</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $booking->vehicle_id ?? '') == $vehicle->id)>{{ $vehicle->registration_number }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('vehicle_id')" />
    </div>
    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach (['appointment', 'walk_in'] as $type)
                <option value="{{ $type }}" @selected(old('type', $booking->type ?? 'appointment') == $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach (['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $booking->status ?? 'pending') == $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="scheduled_at" value="Scheduled At" />
        <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('scheduled_at', isset($booking->scheduled_at) ? $booking->scheduled_at->format('Y-m-d\TH:i') : '')" />
        <x-input-error :messages="$errors->get('scheduled_at')" />
    </div>
    <div>
        <x-input-label for="ends_at" value="Ends At" />
        <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('ends_at', isset($booking->ends_at) ? $booking->ends_at->format('Y-m-d\TH:i') : '')" />
        <x-input-error :messages="$errors->get('ends_at')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label value="Services" />
        <div class="mt-2 flex flex-wrap gap-3">
            @foreach ($services as $service)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <input type="checkbox" name="services[]" value="{{ $service->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(in_array($service->id, old('services', $booking?->services->pluck('id')->toArray() ?? [])))>
                    <span class="text-sm">{{ $service->name }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('services')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('notes', $booking->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>
