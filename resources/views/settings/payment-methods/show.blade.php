<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Settings</span></x-slot>

    <x-ui.section-header eyebrow="Settings" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('settings.payment-methods.index'),
            'editRoute' => route('settings.payment-methods.edit', $paymentMethod),
            'deleteRoute' => route('settings.payment-methods.destroy', $paymentMethod),
            'deleteConfirm' => 'Delete this payment method?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Slug</dt><dd class="font-medium">{{ $paymentMethod->slug }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($paymentMethod->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
