<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="asp-form">
    @csrf
    @method('patch')

    <x-ui.form-section>
        <div class="asp-form-grid">
            <x-ui.form-field label="{{ __('Name') }}" for="name" name="name" :required="true" :col-span="2">
                <x-ui.input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            </x-ui.form-field>

            <x-ui.form-field label="{{ __('Email') }}" for="email" name="email" :required="true" :col-span="2">
                <x-ui.input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="asp-field-hint mt-2">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="text-brand-primary-dim hover:underline dark:text-brand-primary">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                @endif
            </x-ui.form-field>
        </div>
    </x-ui.form-section>

    <x-ui.form-actions>
        <button type="submit" class="asp-btn asp-btn-primary min-w-[8rem]">
            <span class="material-symbols-outlined text-lg">save</span>
            {{ __('Save') }}
        </button>

        @if (session('status') === 'profile-updated')
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
