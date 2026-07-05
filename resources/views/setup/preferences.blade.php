<x-setup-layout title="Preferences" :step="6">
    <main class="auth-main setup-main-single">
        <section class="auth-form-section setup-form-section-full">
            <div class="login-card setup-card setup-card-wide">
                @include('setup._progress', ['step' => 6])

                <h3>Preferences</h3>
                <p class="login-card-subtitle">Configure optional features now or change them later under Settings → Company.</p>

                <form method="POST" action="{{ route('setup.preferences.store') }}" class="auth-form">
                    @csrf

                    <div class="setup-preferences">
                        <label class="setup-checkbox-row">
                            <input type="checkbox" name="sms_notifications_enabled" value="1" @checked(old('sms_notifications_enabled', $data['sms_notifications_enabled'] ?? false))>
                            <span>Enable SMS notifications for vehicle updates</span>
                        </label>

                        <label class="setup-checkbox-row">
                            <input type="checkbox" name="commissions_enabled" value="1" @checked(old('commissions_enabled', $data['commissions_enabled'] ?? false))>
                            <span>Enable staff commissions</span>
                        </label>

                        <div class="setup-form-grid">
                            <div class="auth-field">
                                <label for="commission_default_rate">Default commission rate (%)</label>
                                <input id="commission_default_rate" name="commission_default_rate" type="number" step="0.01" min="0" max="100" class="auth-input setup-input-plain" value="{{ old('commission_default_rate', $data['commission_default_rate'] ?? 10) }}">
                                @error('commission_default_rate')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="commission_trigger">Commission trigger</label>
                                <select id="commission_trigger" name="commission_trigger" class="auth-input setup-input-plain setup-select">
                                    @foreach ($commissionTriggerOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('commission_trigger', $data['commission_trigger'] ?? 'pos_checkout') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('commission_trigger')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="setup-actions">
                        <a href="{{ route('setup.team') }}" class="setup-btn-secondary">Back</a>
                        <button type="submit" class="auth-submit setup-btn-primary">Complete setup</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('setup.preferences.skip') }}" class="setup-skip-form">
                    @csrf
                    <button type="submit" class="setup-link-btn">Skip and use defaults</button>
                </form>
            </div>
        </section>
    </main>
</x-setup-layout>
