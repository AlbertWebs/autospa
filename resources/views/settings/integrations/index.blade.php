<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Settings</span></x-slot>

    <x-ui.section-header eyebrow="Settings" />

    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('settings.integrations.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            @foreach ($integrations as $provider => $integration)
                @php
                    $label = str_replace('_', ' ', $provider);
                    $isSms = $provider === 'sms';
                @endphp

                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <h3 class="mb-1 font-semibold capitalize">{{ $label }}</h3>
                    @if ($isSms)
                        <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">
                            Sends vehicle notifications via
                            <a href="https://rebuetext.com/docs/1.0/overview" class="text-indigo-600 hover:underline dark:text-indigo-400" target="_blank" rel="noopener">RebueText</a>.
                            Also enable SMS under Settings → Company.
                        </p>
                    @endif

                    <label class="mb-3 flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="integrations[{{ $provider }}][enabled]"
                            value="1"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600"
                            @checked(old("integrations.{$provider}.enabled", $integration->is_enabled))
                        >
                        <span class="text-sm">Enabled</span>
                    </label>

                    @if ($isSms)
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="integrations_{{ $provider }}_driver" value="Driver" />
                                <select
                                    id="integrations_{{ $provider }}_driver"
                                    name="integrations[{{ $provider }}][driver]"
                                    class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                >
                                    <option value="stub" @selected(old("integrations.{$provider}.driver", $integration->driver) === 'stub')>Stub (no send)</option>
                                    <option value="rebuetext" @selected(old("integrations.{$provider}.driver", $integration->driver) === 'rebuetext')>RebueText</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label for="integrations_{{ $provider }}_access_token" value="Access Token" />
                                <x-text-input
                                    id="integrations_{{ $provider }}_access_token"
                                    name="integrations[{{ $provider }}][access_token]"
                                    type="password"
                                    class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    placeholder="{{ filled($integration->credentials['access_token'] ?? null) ? '••••••••••••••••' : 'Paste RebueText bearer token' }}"
                                />
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave blank to keep the saved token. You can also set <code class="text-[11px]">REBUETEXT_ACCESS_TOKEN</code> in <code class="text-[11px]">.env</code>.</p>
                            </div>

                            <div>
                                <x-input-label for="integrations_{{ $provider }}_sender_id" value="Sender ID" />
                                <x-text-input
                                    id="integrations_{{ $provider }}_sender_id"
                                    name="integrations[{{ $provider }}][sender_id]"
                                    class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    :value="old('integrations.'.$provider.'.sender_id', $integration->settings['sender_id'] ?? \App\Models\Setting::getValue('sms', 'sender_id', 'AUTOSPA'))"
                                    maxlength="11"
                                />
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Must be approved on your RebueText account.</p>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="integrations[{{ $provider }}][driver]" value="{{ $integration->driver }}">
                        <x-input-label for="integrations_{{ $provider }}_api_key" value="API Key" />
                        <x-text-input
                            id="integrations_{{ $provider }}_api_key"
                            name="integrations[{{ $provider }}][access_token]"
                            class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            :value="old('integrations.'.$provider.'.access_token', $integration->credentials['access_token'] ?? '')"
                        />
                    @endif
                </div>
            @endforeach

            <div class="flex items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                <x-primary-button>Save Integrations</x-primary-button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
