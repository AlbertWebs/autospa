@php $user = $user ?? null; @endphp

<x-ui.form-section title="User Account" description="Account details, branch assignment, roles, and credentials.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Full Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $user->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email" :required="true">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $user->email ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone">
            <x-ui.input id="phone" name="phone" :value="old('phone', $user->phone ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Branch" for="branch_id" name="branch_id" :required="true">
            <x-ui.select id="branch_id" name="branch_id" required>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id ?? '') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field :label="$user ? 'New Password (leave blank to keep)' : 'Password'" for="password" name="password">
            <x-ui.input id="password" name="password" type="password" :required="!$user" />
        </x-ui.form-field>

        <x-ui.form-field label="Confirm Password" for="password_confirmation" name="password_confirmation">
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" />
        </x-ui.form-field>

        <x-ui.form-field label="Roles" name="roles" :col-span="2">
            <div class="asp-checkbox-group">
                @foreach ($roles as $role)
                    <x-ui.checkbox-card name="roles[]" :value="$role->id" :checked="in_array($role->id, old('roles', $user?->roles->pluck('id')->toArray() ?? []))">
                        {{ $role->name }}
                    </x-ui.checkbox-card>
                @endforeach
            </div>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $user->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
