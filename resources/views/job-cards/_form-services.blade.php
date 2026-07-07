<x-ui.form-field
    label="Services"
    name="service_ids"
    :required="true"
    :col-span="2"
    :ajax="$ajax"
    hint="Choose at least one service."
>
    @if ($servicesCollection->isEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            No active services found for this branch.
            <a href="{{ route('services.create') }}" class="font-semibold underline">Add services</a> first.
        </div>
    @else
        <div class="space-y-5">
            @foreach ($servicesByCategory as $categoryName => $categoryServices)
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $categoryName }}</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($categoryServices as $service)
                            <label class="asp-service-pick-card">
                                <input
                                    type="checkbox"
                                    name="service_ids[]"
                                    value="{{ $service->id }}"
                                    class="asp-checkbox"
                                    @checked(in_array($service->id, $selectedServiceIds, false))
                                    @if ($ajax)
                                        :checked="selectedServiceIds.includes({{ $service->id }})"
                                        @change="toggleService({{ $service->id }}, $event.target.checked)"
                                        x-bind:class="{ 'asp-input--error': errors?.service_ids }"
                                    @endif
                                >
                                <span class="asp-service-pick-content">
                                    <span class="asp-service-pick-icon">
                                        <span class="material-symbols-outlined text-[20px]">local_car_wash</span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $service->name }}</span>
                                        <span class="mt-0.5 block font-mono text-xs text-slate-500 dark:text-slate-400">
                                            KES {{ number_format($service->price, 0) }}
                                            @if ($service->duration_minutes)
                                                · {{ $service->duration_minutes }} min
                                            @endif
                                        </span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if ($ajax)
            <p class="asp-field-error" x-show="errors?.service_ids" x-cloak>
                <span class="material-symbols-outlined text-sm">error</span>
                <span x-text="errors?.service_ids?.[0]"></span>
            </p>
        @else
            @error('service_ids')
                <p class="asp-field-error">
                    <span class="material-symbols-outlined text-sm">error</span>
                    {{ $message }}
                </p>
            @enderror
            @error('service_ids.*')
                <p class="asp-field-error">
                    <span class="material-symbols-outlined text-sm">error</span>
                    {{ $message }}
                </p>
            @enderror
        @endif
    @endif
</x-ui.form-field>
