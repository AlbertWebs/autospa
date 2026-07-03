<?php

$base = dirname(__DIR__) . '/resources/views';
$ic = 'mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white';
$sc = $ic;

$files = [];

// Helper snippets
$th = fn($t) => '<th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">' . $t . '</th>';
$thR = fn($t) => '<th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">' . $t . '</th>';

// ========== SETTINGS: COMPANY ==========
$files['settings/company/edit.blade.php'] = <<<BLADE
<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Company Settings</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-6">
            @csrf @method('PUT')
            <div class="grid gap-6 sm:grid-cols-2">
                <div><x-input-label for="name" value="Company Name" /><x-text-input id="name" name="name" class="{$ic}" :value="old('name', \$company->name)" required /><x-input-error :messages="\$errors->get('name')" /></div>
                <div><x-input-label for="legal_name" value="Legal Name" /><x-text-input id="legal_name" name="legal_name" class="{$ic}" :value="old('legal_name', \$company->legal_name)" /><x-input-error :messages="\$errors->get('legal_name')" /></div>
                <div><x-input-label for="registration_number" value="Registration Number" /><x-text-input id="registration_number" name="registration_number" class="{$ic}" :value="old('registration_number', \$company->registration_number)" /><x-input-error :messages="\$errors->get('registration_number')" /></div>
                <div><x-input-label for="tax_number" value="Tax Number" /><x-text-input id="tax_number" name="tax_number" class="{$ic}" :value="old('tax_number', \$company->tax_number)" /><x-input-error :messages="\$errors->get('tax_number')" /></div>
                <div class="sm:col-span-2"><x-input-label for="address" value="Address" /><textarea id="address" name="address" rows="2" class="{$ic}">{{ old('address', \$company->address) }}</textarea><x-input-error :messages="\$errors->get('address')" /></div>
                <div><x-input-label for="phone" value="Phone" /><x-text-input id="phone" name="phone" class="{$ic}" :value="old('phone', \$company->phone)" /><x-input-error :messages="\$errors->get('phone')" /></div>
                <div><x-input-label for="email" value="Email" /><x-text-input id="email" name="email" type="email" class="{$ic}" :value="old('email', \$company->email)" /><x-input-error :messages="\$errors->get('email')" /></div>
                <div class="sm:col-span-2"><x-input-label for="website" value="Website" /><x-text-input id="website" name="website" class="{$ic}" :value="old('website', \$company->website)" /><x-input-error :messages="\$errors->get('website')" /></div>
            </div>
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Company Details</x-primary-button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
BLADE;

// ========== SETTINGS: BRANCHES ==========
$files['settings/branches/_form.blade.php'] = <<<BLADE
@php \$branch = \$branch ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div><x-input-label for="name" value="Branch Name" /><x-text-input id="name" name="name" class="{$ic}" :value="old('name', \$branch->name ?? '')" required /><x-input-error :messages="\$errors->get('name')" /></div>
    <div><x-input-label for="code" value="Branch Code" /><x-text-input id="code" name="code" class="{$ic}" :value="old('code', \$branch->code ?? '')" required /><x-input-error :messages="\$errors->get('code')" /></div>
    <div class="sm:col-span-2"><x-input-label for="address" value="Address" /><textarea id="address" name="address" rows="2" class="{$ic}">{{ old('address', \$branch->address ?? '') }}</textarea><x-input-error :messages="\$errors->get('address')" /></div>
    <div><x-input-label for="phone" value="Phone" /><x-text-input id="phone" name="phone" class="{$ic}" :value="old('phone', \$branch->phone ?? '')" /><x-input-error :messages="\$errors->get('phone')" /></div>
    <div><x-input-label for="email" value="Email" /><x-text-input id="email" name="email" type="email" class="{$ic}" :value="old('email', \$branch->email ?? '')" /><x-input-error :messages="\$errors->get('email')" /></div>
    <div class="sm:col-span-2"><label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', \$branch->is_active ?? true))><span class="text-sm text-slate-700 dark:text-slate-300">Active</span></label></div>
</div>
BLADE;

$branchIndex = function ($title = 'Branches') use ($ic) {
    return <<<BLADE
<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{$title}</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('settings.branches.create'), 'createLabel' => 'Add Branch'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">{$title}</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse (\$branches as \$branch)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ \$branch->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ \$branch->code }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ \$branch->phone ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">@if(\$branch->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('settings.branches.show', \$branch) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('settings.branches.edit', \$branch) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if (\$branches->isEmpty())<x-ui.empty-state title="No branches yet" description="Add your first branch location." />@endif
        @include('partials.crud.pagination', ['paginator' => \$branches])
    </x-ui.card>
</x-layouts.app>
BLADE;
};
$files['settings/branches/index.blade.php'] = $branchIndex();

$files['settings/branches/create.blade.php'] = <<<BLADE
<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Add Branch</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.branches.store') }}" class="space-y-6">
            @csrf @include('settings.branches._form')
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Create Branch</x-primary-button>
                <a href="{{ route('settings.branches.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
BLADE;

$files['settings/branches/edit.blade.php'] = <<<BLADE
<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Branch</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.branches.update', \$branch) }}" class="space-y-6">
            @csrf @method('PUT') @include('settings.branches._form', ['branch' => \$branch])
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Update Branch</x-primary-button>
                <a href="{{ route('settings.branches.show', \$branch) }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
BLADE;

$files['settings/branches/show.blade.php'] = <<<BLADE
<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ \$branch->name }}</h1></x-slot>
    <div class="mb-6">@include('partials.crud.show-actions', ['backRoute' => route('settings.branches.index'), 'editRoute' => route('settings.branches.edit', \$branch), 'deleteRoute' => route('settings.branches.destroy', \$branch)])</div>
    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Code</dt><dd class="font-medium">{{ \$branch->code }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Address</dt><dd>{{ \$branch->address ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ \$branch->phone ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ \$branch->email ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if(\$branch->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
BLADE;

// Continue in part 2 - write remaining files via append
$count = 0;
$skipped = 0;
foreach ($files as $path => $content) {
    $full = $base . '/' . $path;
    if (str_contains($path, 'settings/users/') || in_array($path, ['layouts/app.blade.php', 'dashboard/index.blade.php'])) {
        $skipped++;
        continue;
    }
    $dir = dirname($full);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if ($path === 'settings/branches/_form.blade.php' && file_exists($full)) {
        echo "SKIP (exists): $path\n";
        $skipped++;
        continue;
    }
    file_put_contents($full, ltrim($content));
    echo "CREATED: $path\n";
    $count++;
}
echo "\nPart 1: Created $count, Skipped $skipped\n";
