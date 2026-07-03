<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Company Settings</h1></x-slot>
    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Company Name" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $company->name)" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="legal_name" value="Legal Name" />
                    <x-text-input id="legal_name" name="legal_name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('legal_name', $company->legal_name)" />
                    <x-input-error :messages="$errors->get('legal_name')" />
                </div>
                <div>
                    <x-input-label for="registration_number" value="Registration Number" />
                    <x-text-input id="registration_number" name="registration_number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('registration_number', $company->registration_number)" />
                    <x-input-error :messages="$errors->get('registration_number')" />
                </div>
                <div>
                    <x-input-label for="tax_number" value="Tax Number" />
                    <x-text-input id="tax_number" name="tax_number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('tax_number', $company->tax_number)" />
                    <x-input-error :messages="$errors->get('tax_number')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="address" value="Address" />
                    <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('address', $company->address) }}</textarea>
                    <x-input-error :messages="$errors->get('address')" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('phone', $company->phone)" />
                    <x-input-error :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('email', $company->email)" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="website" value="Website" />
                    <x-text-input id="website" name="website" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('website', $company->website)" />
                    <x-input-error :messages="$errors->get('website')" />
                </div>
            </div>
            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Company Details</x-primary-button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
