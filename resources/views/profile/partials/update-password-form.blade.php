<form method="post" action="{{ route('password.update') }}" class="asp-form">
    @csrf
    @method('put')

    <x-ui.form-section>
        <div class="asp-form-grid">
            <x-ui.form-field label="{{ __('Current Password') }}" for="update_password_current_password" :col-span="2">
                <x-ui.input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
                @error('current_password', 'updatePassword')
                    <p class="asp-field-error">
                        <span class="material-symbols-outlined text-sm">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </x-ui.form-field>

            <x-ui.form-field label="{{ __('New Password') }}" for="update_password_password" :col-span="2">
                <x-ui.input id="update_password_password" name="password" type="password" autocomplete="new-password" />
                @error('password', 'updatePassword')
                    <p class="asp-field-error">
                        <span class="material-symbols-outlined text-sm">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </x-ui.form-field>

            <x-ui.form-field label="{{ __('Confirm Password') }}" for="update_password_password_confirmation" :col-span="2">
                <x-ui.input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                @error('password_confirmation', 'updatePassword')
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
            {{ __('Save') }}
        </button>

        @if (session('status') === 'password-updated')
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
