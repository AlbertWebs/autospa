<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Settings</span></x-slot>

    <x-ui.section-header class="mb-6" eyebrow="Settings" />

    @if ($details = session('test_result.details'))
        <x-ui.card class="mb-6">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Last test details</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                @foreach ($details as $key => $value)
                    @if (filled($value))
                        <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800">
                            <dt class="text-slate-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                            <dd class="font-mono text-right">{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </x-ui.card>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Email</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Driver: <span class="font-mono">{{ $status['email']['driver'] }}</span>
                    </p>
                </div>
                <x-ui.badge :color="$status['email']['enabled'] ? 'green' : 'amber'">
                    {{ $status['email']['enabled'] ? 'Notifications on' : 'Notifications off' }}
                </x-ui.badge>
            </div>
            <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Test emails bypass the notification toggle so you can verify SMTP/mail settings.</p>
            <form method="POST" action="{{ route('settings.test-ground.send') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="channel" value="email">
                <x-ui.form-field label="To" for="email_recipient" name="recipient" :required="true">
                    <x-ui.input id="email_recipient" name="recipient" type="email" :value="old('recipient', auth()->user()->email)" required />
                </x-ui.form-field>
                <x-ui.form-field label="Subject" for="email_subject" name="subject">
                    <x-ui.input id="email_subject" name="subject" :value="old('subject', 'AutoSpa test email')" />
                </x-ui.form-field>
                <x-ui.form-field label="Message" for="email_message" name="message" :required="true">
                    <x-ui.textarea id="email_message" name="message" rows="3" required>This is a test email from AutoSpa.</x-ui.textarea>
                </x-ui.form-field>
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Send test email</button>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">SMS</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Driver: <span class="font-mono">{{ $status['sms']['driver'] }}</span>
                    </p>
                </div>
                <x-ui.badge :color="$status['sms']['enabled'] ? 'green' : 'slate'">
                    {{ $status['sms']['enabled'] ? 'Enabled' : 'Disabled' }}
                </x-ui.badge>
            </div>
            <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Configure SMS under Settings → Integrations. Stub driver returns success without sending.</p>
            <form method="POST" action="{{ route('settings.test-ground.send') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="channel" value="sms">
                <x-ui.form-field label="Phone" for="sms_recipient" name="recipient" :required="true">
                    <x-ui.input id="sms_recipient" name="recipient" type="tel" placeholder="0712345678" :value="old('recipient')" required />
                </x-ui.form-field>
                <x-ui.form-field label="Message" for="sms_message" name="message" :required="true">
                    <x-ui.textarea id="sms_message" name="message" rows="3" required>AutoSpa SMS test message.</x-ui.textarea>
                </x-ui.form-field>
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Send test SMS</button>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">WhatsApp</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Driver: <span class="font-mono">{{ $status['whatsapp']['driver'] }}</span>
                    </p>
                </div>
                <x-ui.badge :color="$status['whatsapp']['enabled'] ? 'green' : 'slate'">
                    {{ $status['whatsapp']['enabled'] ? 'Enabled' : 'Disabled' }}
                </x-ui.badge>
            </div>
            <form method="POST" action="{{ route('settings.test-ground.send') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="channel" value="whatsapp">
                <x-ui.form-field label="Phone" for="whatsapp_recipient" name="recipient" :required="true">
                    <x-ui.input id="whatsapp_recipient" name="recipient" type="tel" placeholder="254712345678" :value="old('recipient')" required />
                </x-ui.form-field>
                <x-ui.form-field label="Message" for="whatsapp_message" name="message" :required="true">
                    <x-ui.textarea id="whatsapp_message" name="message" rows="3" required>AutoSpa WhatsApp test message.</x-ui.textarea>
                </x-ui.form-field>
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Send test WhatsApp</button>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">M-Pesa STK Push</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Driver: <span class="font-mono">{{ $status['mpesa']['driver'] }}</span>
                    </p>
                </div>
                <x-ui.badge :color="$status['mpesa']['enabled'] ? 'green' : 'slate'">
                    {{ $status['mpesa']['enabled'] ? 'Enabled' : 'Disabled' }}
                </x-ui.badge>
            </div>
            <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Sends a test STK push. Use a sandbox phone in development.</p>
            <form method="POST" action="{{ route('settings.test-ground.send') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="channel" value="mpesa">
                <x-ui.form-field label="Phone" for="mpesa_recipient" name="recipient" :required="true">
                    <x-ui.input id="mpesa_recipient" name="recipient" type="tel" placeholder="254712345678" :value="old('recipient')" required />
                </x-ui.form-field>
                <x-ui.form-field label="Amount (KES)" for="mpesa_amount" name="amount" :required="true">
                    <x-ui.input id="mpesa_amount" name="amount" type="number" min="1" step="1" :value="old('amount', 1)" required />
                </x-ui.form-field>
                <button type="submit" class="asp-btn asp-btn-primary !py-2.5">Send test STK push</button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
