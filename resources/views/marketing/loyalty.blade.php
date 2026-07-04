<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Loyalty Program</h1></x-slot>
    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.stat-card label="Total Members" value="N/A" />
        <x-ui.stat-card label="Points Issued" value="N/A" />
        <x-ui.stat-card label="Points Redeemed" value="N/A" />
    </div>
    <x-ui.card class="mt-6">
        <h2 class="mb-4 text-lg font-semibold">Loyalty Program Settings</h2>
        <p class="text-sm text-slate-500">Configure your loyalty program rules and rewards. Visit customer loyalty transactions for detailed history.</p>
        <div class="mt-4">
            <a href="{{ route('customers.loyalty') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View loyalty transactions →</a>
        </div>
    </x-ui.card>
</x-layouts.app>
