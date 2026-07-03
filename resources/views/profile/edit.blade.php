<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Profile</span>
    </x-slot>

    <header class="asp-page-header">
        <div>
            <p class="asp-page-eyebrow">Account</p>
            <h1 class="asp-page-title">Profile Settings</h1>
            <p class="asp-page-subtitle">Manage your account information and security.</p>
        </div>
    </header>

    <div class="max-w-3xl space-y-6">
        <div class="asp-panel">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Profile Information</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Update your name and email address.</p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">person</span>
            </div>
            <div class="asp-panel-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="asp-panel">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Update Password</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Use a long, random password to stay secure.</p>
                </div>
                <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">lock</span>
            </div>
            <div class="asp-panel-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="asp-panel">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title">Delete Account</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Permanently remove your account and all data.</p>
                </div>
                <span class="material-symbols-outlined text-rose-500">delete_forever</span>
            </div>
            <div class="asp-panel-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-layouts.app>
