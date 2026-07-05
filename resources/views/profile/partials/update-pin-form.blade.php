<form method="post" action="{{ route('pin.update') }}" class="asp-form">
    @csrf
    @method('put')

    <x-ui.form-section>
        <div class="asp-form-grid">
            @if (auth()->user()->pin)
                <x-ui.form-field label="Current PIN" for="update_pin_current_pin" :col-span="2">
                    <x-ui.input
                        id="update_pin_current_pin"
                        name="current_pin"
                        type="password"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        autocomplete="off"
                    />
                    @error('current_pin', 'updatePin')
                        <p class="asp-field-error">
                            <span class="material-symbols-outlined text-sm">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </x-ui.form-field>
            @endif

            <x-ui.form-field label="New PIN" for="update_pin_pin" :col-span="2" hint="Use 4–6 digits for quick sign-in.">
                <x-ui.input
                    id="update_pin_pin"
                    name="pin"
                    type="password"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    autocomplete="new-password"
                />
                @error('pin', 'updatePin')
                    <p class="asp-field-error">
                        <span class="material-symbols-outlined text-sm">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </x-ui.form-field>

            <x-ui.form-field label="Confirm PIN" for="update_pin_pin_confirmation" :col-span="2">
                <x-ui.input
                    id="update_pin_pin_confirmation"
                    name="pin_confirmation"
                    type="password"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    autocomplete="new-password"
                />
                @error('pin_confirmation', 'updatePin')
                    <p class="asp-field-error">
                        <span class="material-symbols-outlined text-sm">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </x-ui.form-field>
        </div>
    </x-ui.form-section>

    <x-ui.form-actions>
        <button type="submit" class="asp-btn asp-btn-primary min-w-[8rem]">
            <span class="material-symbols-outlined text-lg">save</span>
            {{ __('Save PIN') }}
        </button>

        @if (session('status') === 'pin-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-slate-500 dark:text-slate-400"
            >{{ __('Saved.') }}</p>
        @endif
    </x-ui.form-actions>
</form>
