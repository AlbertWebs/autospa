<x-layouts.mobile title="Integrations">
    <x-mobile.page-header title="Integrations" :back="route('mobile.menu')" />
    <div class="asp-mobile-card">
        <p class="text-sm text-slate-600 dark:text-slate-300">Configure SMS, M-Pesa, and other integrations from the desktop settings page.</p>
    </div>
    <a href="{{ route('settings.integrations.index') }}" class="asp-mobile-action-btn mt-4 inline-flex w-full justify-center">Open integrations</a>
</x-layouts.mobile>
