<x-ui.form-page
    eyebrow="Settings"
    title="Business Hours"
    subtitle="Set opening and closing times for each day of the week."
    panel-title="Weekly Schedule"
    panel-icon="schedule"
    :action="route('settings.business-hours.update')"
    method="PUT"
    submit-label="Save Business Hours"
>
    <x-ui.form-section title="Operating Hours" description="Mark days as closed or set open and close times.">
        <div class="space-y-4">
            @foreach ($businessHours as $day => $hours)
                <div class="grid gap-4 rounded-xl border border-slate-200/80 p-4 sm:grid-cols-4 dark:border-brand-border/60">
                    <div class="flex items-center font-medium capitalize text-slate-900 dark:text-white">{{ $day }}</div>

                    <x-ui.form-field label="Opens" :for="$day . '_open'" :name="'hours.' . $day . '.open'">
                        <x-ui.input
                            id="{{ $day }}_open"
                            name="hours[{{ $day }}][open]"
                            type="time"
                            :value="old('hours.'.$day.'.open', $hours['open'] ?? '')"
                        />
                    </x-ui.form-field>

                    <x-ui.form-field label="Closes" :for="$day . '_close'" :name="'hours.' . $day . '.close'">
                        <x-ui.input
                            id="{{ $day }}_close"
                            name="hours[{{ $day }}][close]"
                            type="time"
                            :value="old('hours.'.$day.'.close', $hours['close'] ?? '')"
                        />
                    </x-ui.form-field>

                    <div class="flex items-end pb-1">
                        <x-ui.checkbox
                            name="hours[{{ $day }}][closed]"
                            :checked="old('hours.'.$day.'.closed', $hours['closed'] ?? false)"
                        >
                            Closed
                        </x-ui.checkbox>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.form-section>
</x-ui.form-page>
