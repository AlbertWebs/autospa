<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profile</h1></x-slot>

    <div class="space-y-6">
        <x-ui.card>
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.update-password-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.delete-user-form')
        </x-ui.card>
    </div>
</x-layouts.app>
