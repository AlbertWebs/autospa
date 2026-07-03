<div>
    <button
        type="button"
        class="asp-btn border border-rose-200/80 bg-white text-rose-600 hover:border-rose-300 hover:bg-rose-50 dark:border-rose-500/30 dark:bg-brand-surface-high dark:text-rose-400 dark:hover:bg-rose-500/10"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <span class="material-symbols-outlined text-lg">delete_forever</span>
        {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="asp-form p-6">
            @csrf
            @method('delete')

            <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-white">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <x-ui.form-field label="{{ __('Password') }}" for="password" class="mt-6">
                <x-ui.input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="{{ __('Password') }}"
                />
                @error('password', 'userDeletion')
                    <p class="asp-field-error">
                        <span class="material-symbols-outlined text-sm">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </x-ui.form-field>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="asp-btn asp-btn-ghost" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="asp-btn border border-rose-200/80 bg-rose-600 text-white hover:bg-rose-700 dark:border-rose-500/30 dark:bg-rose-600 dark:hover:bg-rose-500">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
