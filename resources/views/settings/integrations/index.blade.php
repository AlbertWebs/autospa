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
                    $isMpesa = $provider === 'mpesa';
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
                    @elseif ($isMpesa)
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="integrations_{{ $provider }}_driver" value="Driver" />
                                <select
                                    id="integrations_{{ $provider }}_driver"
                                    name="integrations[{{ $provider }}][driver]"
                                    class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                >
                                    <option value="stub" @selected(old("integrations.{$provider}.driver", $integration->driver) === 'stub')>Stub (no send)</option>
                                    <option value="daraja" @selected(old("integrations.{$provider}.driver", $integration->driver) === 'daraja')>Safaricom Daraja</option>
                                </select>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_consumer_key" value="Consumer Key" />
                                    <x-text-input id="integrations_{{ $provider }}_consumer_key" name="integrations[{{ $provider }}][consumer_key]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.consumer_key', $integration->credentials['consumer_key'] ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_consumer_secret" value="Consumer Secret" />
                                    <x-text-input id="integrations_{{ $provider }}_consumer_secret" name="integrations[{{ $provider }}][consumer_secret]" type="password" class="mt-1 block w-full" placeholder="{{ filled($integration->credentials['consumer_secret'] ?? null) ? '••••••••••••••••' : 'Paste Daraja consumer secret' }}" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_shortcode" value="Shortcode / Paybill" />
                                    <x-text-input id="integrations_{{ $provider }}_shortcode" name="integrations[{{ $provider }}][shortcode]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.shortcode', $integration->credentials['shortcode'] ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_passkey" value="Lipa Na M-Pesa Passkey" />
                                    <x-text-input id="integrations_{{ $provider }}_passkey" name="integrations[{{ $provider }}][passkey]" type="password" class="mt-1 block w-full" placeholder="{{ filled($integration->credentials['passkey'] ?? null) ? '••••••••••••••••' : 'Paste LNM passkey' }}" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_initiator_name" value="B2C Initiator Name" />
                                    <x-text-input id="integrations_{{ $provider }}_initiator_name" name="integrations[{{ $provider }}][initiator_name]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.initiator_name', $integration->credentials['initiator_name'] ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_security_credential" value="Security Credential" />
                                    <x-text-input id="integrations_{{ $provider }}_security_credential" name="integrations[{{ $provider }}][security_credential]" type="password" class="mt-1 block w-full" placeholder="{{ filled($integration->credentials['security_credential'] ?? null) ? '••••••••••••••••' : 'Paste encrypted initiator password' }}" />
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label for="integrations_{{ $provider }}_base_url" value="Daraja Base URL" />
                                    <x-text-input id="integrations_{{ $provider }}_base_url" name="integrations[{{ $provider }}][base_url]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.base_url', $integration->settings['base_url'] ?? config('integrations.mpesa.daraja.base_url'))" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_stk_result_url" value="STK Result URL" />
                                    <x-text-input id="integrations_{{ $provider }}_stk_result_url" name="integrations[{{ $provider }}][stk_result_url]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.stk_result_url', $integration->settings['stk_result_url'] ?? config('integrations.mpesa.daraja.stk_result_url'))" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_result_url" value="B2C Result URL" />
                                    <x-text-input id="integrations_{{ $provider }}_result_url" name="integrations[{{ $provider }}][result_url]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.result_url', $integration->settings['result_url'] ?? config('integrations.mpesa.daraja.result_url'))" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_queue_timeout_url" value="Queue Timeout URL" />
                                    <x-text-input id="integrations_{{ $provider }}_queue_timeout_url" name="integrations[{{ $provider }}][queue_timeout_url]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.queue_timeout_url', $integration->settings['queue_timeout_url'] ?? config('integrations.mpesa.daraja.queue_timeout_url'))" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_balance_result_url" value="Balance Result URL" />
                                    <x-text-input id="integrations_{{ $provider }}_balance_result_url" name="integrations[{{ $provider }}][balance_result_url]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.balance_result_url', $integration->settings['balance_result_url'] ?? config('integrations.mpesa.daraja.balance_result_url'))" />
                                </div>
                                <div>
                                    <x-input-label for="integrations_{{ $provider }}_balance_timeout_url" value="Balance Timeout URL" />
                                    <x-text-input id="integrations_{{ $provider }}_balance_timeout_url" name="integrations[{{ $provider }}][balance_timeout_url]" class="mt-1 block w-full" :value="old('integrations.'.$provider.'.balance_timeout_url', $integration->settings['balance_timeout_url'] ?? config('integrations.mpesa.daraja.balance_timeout_url'))" />
                                </div>
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
