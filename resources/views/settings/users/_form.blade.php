@php $user = $user ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Full Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $user->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('email', $user->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" />
    </div>
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('phone', $user->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" />
    </div>
    <div>
        <x-input-label for="branch_id" value="Branch" />
        <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id ?? '') == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('branch_id')" />
    </div>
    <div>
        <x-input-label for="password" value="{{ $user ? 'New Password (leave blank to keep)' : 'Password' }}" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :required="!$user" />
        <x-input-error :messages="$errors->get('password')" />
    </div>
    <div>
        <x-input-label for="password_confirmation" value="Confirm Password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label value="Roles" />
        <div class="mt-2 flex flex-wrap gap-3">
            @foreach ($roles as $role)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(in_array($role->id, old('roles', $user?->roles->pluck('id')->toArray() ?? [])))>
                    <span class="text-sm">{{ $role->name }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('roles')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $user->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>
