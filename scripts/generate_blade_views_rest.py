#!/usr/bin/env python3
"""Generate remaining AutoSpa Blade view files (part 2)."""
import os
import sys

sys.path.insert(0, os.path.dirname(__file__))
from generate_blade_views import (
    IC, write, created, layout, create_page, edit_page, show_page,
    show_page_view_only, index_header,
)

# ============ PAYMENT METHODS ============
write("settings/payment-methods/_form.blade.php", f"""@php $paymentMethod = $paymentMethod ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $paymentMethod->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="slug" value="Slug" />
        <x-text-input id="slug" name="slug" class="{IC}" :value="old('slug', $paymentMethod->slug ?? '')" required />
        <x-input-error :messages="$errors->get('slug')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $paymentMethod->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

write("settings/payment-methods/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Payment Methods</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('settings.payment-methods.create'), 'createLabel' => 'Add Payment Method'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Payment Methods</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($paymentMethods as $paymentMethod)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $paymentMethod->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $paymentMethod->slug }}</td>
                            <td class="whitespace-nowrap px-6 py-4">@if($paymentMethod->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('settings.payment-methods.show', $paymentMethod) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('settings.payment-methods.edit', $paymentMethod) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($paymentMethods->isEmpty())<x-ui.empty-state title="No payment methods" description="Configure how customers can pay." />@endif
        @include('partials.crud.pagination', ['paginator' => $paymentMethods])
    </x-ui.card>
</x-layouts.app>""")

write("settings/payment-methods/create.blade.php", create_page("Add Payment Method", "settings.payment-methods.store", "settings.payment-methods.index", "settings.payment-methods._form", "Create Payment Method"))
write("settings/payment-methods/edit.blade.php", edit_page("Edit Payment Method", "settings.payment-methods.update", "settings.payment-methods.show", "settings.payment-methods._form", "paymentMethod", "Save Changes"))
write("settings/payment-methods/show.blade.php", show_page("{{ $paymentMethod->name }}", "settings.payment-methods.index", "settings.payment-methods.edit", "settings.payment-methods.destroy", "Delete this payment method?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Slug</dt><dd class="font-medium">{{ $paymentMethod->slug }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($paymentMethod->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ INTEGRATIONS & BUSINESS HOURS ============
write("settings/integrations/index.blade.php", layout("Integrations", """    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.integrations.update') }}" class="space-y-6">
            @csrf @method('PUT')
            @foreach ($integrations as $key => $integration)
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <h3 class="mb-3 font-semibold capitalize">{{ str_replace('_', ' ', $key) }}</h3>
                    <label class="mb-3 flex items-center gap-2">
                        <input type="checkbox" name="integrations[{{ $key }}][enabled]" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('integrations.'.$key.'.enabled', $integration['enabled'] ?? false))>
                        <span class="text-sm">Enabled</span>
                    </label>
                    <x-input-label for="integrations_{{ $key }}_api_key" value="API Key" />
                    <x-text-input id="integrations_{{ $key }}_api_key" name="integrations[{{ $key }}][api_key]" class=\"""" + IC + """\" :value="old('integrations.'.$key.'.api_key', $integration['api_key'] ?? '')" />
                </div>
            @endforeach
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Integrations</x-primary-button>
            </div>
        </form>
    </x-ui.card>"""))

write("settings/business-hours/edit.blade.php", layout("Business Hours", """    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.business-hours.update') }}" class="space-y-6">
            @csrf @method('PUT')
            @foreach ($businessHours as $day => $hours)
                <div class="grid gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-4 dark:border-slate-700">
                    <div class="font-medium capitalize">{{ $day }}</div>
                    <div>
                        <x-input-label for="{{ $day }}_open" value="Opens" />
                        <x-text-input id="{{ $day }}_open" name="hours[{{ $day }}][open]" type="time" class=\"""" + IC + """\" :value="old('hours.'.$day.'.open', $hours['open'] ?? '')" />
                    </div>
                    <div>
                        <x-input-label for="{{ $day }}_close" value="Closes" />
                        <x-text-input id="{{ $day }}_close" name="hours[{{ $day }}][close]" type="time" class=\"""" + IC + """\" :value="old('hours.'.$day.'.close', $hours['close'] ?? '')" />
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 pb-2">
                            <input type="checkbox" name="hours[{{ $day }}][closed]" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('hours.'.$day.'.closed', $hours['closed'] ?? false))>
                            <span class="text-sm">Closed</span>
                        </label>
                    </div>
                </div>
            @endforeach
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Business Hours</x-primary-button>
            </div>
        </form>
    </x-ui.card>"""))

# ============ CUSTOMERS ============
write("customers/_form.blade.php", f"""@php $customer = $customer ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="full_name" value="Full Name" />
        <x-text-input id="full_name" name="full_name" class="{IC}" :value="old('full_name', $customer->full_name ?? '')" required />
        <x-input-error :messages="$errors->get('full_name')" />
    </div>
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="{IC}" :value="old('phone', $customer->phone ?? '')" required />
        <x-input-error :messages="$errors->get('phone')" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="{IC}" :value="old('email', $customer->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" />
    </div>
    <div>
        <x-input-label for="id_number" value="ID Number" />
        <x-text-input id="id_number" name="id_number" class="{IC}" :value="old('id_number', $customer->id_number ?? '')" />
        <x-input-error :messages="$errors->get('id_number')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="2" class="{IC}">{{ old('address', $customer->address ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('address')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="{IC}">{{ old('notes', $customer->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>""")

write("customers/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Customers</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('customers.create'), 'createLabel' => 'Add Customer'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Customers</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Email</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $customer->full_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $customer->phone }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $customer->email ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('customers.show', $customer) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('customers.edit', $customer) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($customers->isEmpty())<x-ui.empty-state title="No customers yet" description="Add your first customer to start booking services." />@endif
        @include('partials.crud.pagination', ['paginator' => $customers])
    </x-ui.card>
</x-layouts.app>""")

write("customers/create.blade.php", create_page("Add Customer", "customers.store", "customers.index", "customers._form", "Create Customer"))
write("customers/edit.blade.php", edit_page("Edit Customer", "customers.update", "customers.show", "customers._form", "customer", "Save Changes"))
write("customers/show.blade.php", show_page("{{ $customer->full_name }}", "customers.index", "customers.edit", "customers.destroy", "Delete this customer?", """    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Contact Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd class="font-medium">{{ $customer->phone }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $customer->email ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">ID Number</dt><dd>{{ $customer->id_number ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Address</dt><dd>{{ $customer->address ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>
        <x-ui.card>
            <h2 class="mb-4 text-lg font-semibold">Notes</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $customer->notes ?? 'No notes.' }}</p>
        </x-ui.card>
    </div>"""))

write("customers/loyalty.blade.php", layout("Loyalty Points", """    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $transaction->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $transaction->type }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $transaction->points }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $transaction->created_at?->format('M j, Y') }}</td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->isEmpty())<x-ui.empty-state title="No loyalty transactions" description="Customer loyalty activity will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $transactions])
    </x-ui.card>"""))

write("customers/feedback.blade.php", layout("Customer Feedback", """    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Feedback</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($notes as $note)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $note->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $note->rating ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ Str::limit($note->content ?? $note->notes ?? '', 80) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $note->created_at?->format('M j, Y') }}</td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($notes->isEmpty())<x-ui.empty-state title="No feedback yet" description="Customer feedback will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $notes])
    </x-ui.card>"""))

print(f"Part 2a: {len(created)} files")
