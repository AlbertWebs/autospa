<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Danger Zone</span>
    </x-slot>

    <x-ui.section-header eyebrow="Account" title="Danger Zone" />

    <div class="mx-auto max-w-3xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="asp-panel border-rose-200/80 dark:border-rose-500/30">
            <div class="asp-panel-header">
                <div>
                    <h2 class="asp-panel-title text-rose-700 dark:text-rose-300">Delete test data</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        Permanently remove selected operational data. Users, roles, branches, company settings, services, products, and employees are kept.
                    </p>
                </div>
                <span class="material-symbols-outlined text-rose-500">warning</span>
            </div>

            <div class="asp-panel-body">
                <form
                    method="POST"
                    action="{{ route('danger-zone.destroy') }}"
                    class="asp-form space-y-6"
                    data-turbo="false"
                    x-data="{
                        toggleAll() {
                            const boxes = [...$refs.groups.querySelectorAll('input[type=checkbox]')];
                            const allChecked = boxes.every((box) => box.checked);
                            boxes.forEach((box) => { box.checked = !allChecked; });
                        }
                    }"
                >
                    @csrf
                    @method('DELETE')

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Data groups</p>
                            <button
                                type="button"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-300"
                                x-on:click="toggleAll()"
                            >
                                Select / clear all
                            </button>
                        </div>

                        <div x-ref="groups" class="divide-y divide-slate-200 rounded-xl border border-slate-200 dark:divide-slate-700 dark:border-slate-700">
                            @foreach ($groups as $key => $group)
                                <label class="flex cursor-pointer items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                    <input
                                        type="checkbox"
                                        name="groups[]"
                                        value="{{ $key }}"
                                        class="mt-1 rounded border-slate-300 text-rose-600 focus:ring-rose-500 dark:border-slate-600 dark:bg-slate-800"
                                        @checked(collect(old('groups', []))->contains($key))
                                    >
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="font-medium text-slate-900 dark:text-white">{{ $group['label'] }}</span>
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                                {{ number_format($group['count']) }} rows
                                            </span>
                                        </span>
                                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $group['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('groups')
                            <p class="asp-field-error">
                                <span class="material-symbols-outlined text-sm">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                        @error('groups.*')
                            <p class="asp-field-error">
                                <span class="material-symbols-outlined text-sm">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <label class="flex items-start gap-3 rounded-xl border border-rose-200/80 bg-rose-50/70 px-4 py-3 text-sm text-rose-900 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-100">
                        <input
                            type="checkbox"
                            name="confirm"
                            value="1"
                            class="mt-0.5 rounded border-rose-300 text-rose-600 focus:ring-rose-500"
                            @checked(old('confirm'))
                        >
                        <span>I understand this permanently deletes the selected data and cannot be undone.</span>
                    </label>
                    @error('confirm')
                        <p class="asp-field-error">
                            <span class="material-symbols-outlined text-sm">error</span>
                            {{ $message }}
                        </p>
                    @enderror

                    <x-ui.form-field label="Confirm with password" for="password">
                        <x-ui.input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Your account password"
                        />
                        @error('password')
                            <p class="asp-field-error">
                                <span class="material-symbols-outlined text-sm">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </x-ui.form-field>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ route('profile.edit') }}" class="asp-btn asp-btn-ghost">Cancel</a>
                        <button type="submit" class="asp-btn border border-rose-200/80 bg-rose-600 text-white hover:bg-rose-700 dark:border-rose-500/30 dark:bg-rose-600 dark:hover:bg-rose-500">
                            <span class="material-symbols-outlined text-lg">delete_forever</span>
                            Delete selected data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
