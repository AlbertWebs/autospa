<x-setup-layout title="Team Members" :step="5">
    <main class="auth-main setup-main-single">
        <section class="auth-form-section setup-form-section-full">
            <div class="login-card setup-card setup-card-wide">
                @include('setup._progress', ['step' => 5])

                <h3>Team members</h3>
                <p class="login-card-subtitle">Optionally add a supervisor and cashier now, or skip and create users later in Settings.</p>

                <form method="POST" action="{{ route('setup.team.store') }}" class="auth-form">
                    @csrf

                    <div class="setup-team-block">
                        <label class="setup-checkbox-row">
                            <input type="checkbox" name="create_supervisor" value="1" @checked(old('create_supervisor', $data['create_supervisor'] ?? false))>
                            <span>Add a supervisor account</span>
                        </label>

                        <div class="setup-form-grid">
                            <div class="auth-field">
                                <label for="supervisor_name">Supervisor name</label>
                                <input id="supervisor_name" name="supervisor_name" class="auth-input setup-input-plain" value="{{ old('supervisor_name', $data['supervisor_name'] ?? '') }}">
                                @error('supervisor_name')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="supervisor_email">Supervisor email</label>
                                <input id="supervisor_email" name="supervisor_email" type="email" class="auth-input setup-input-plain" value="{{ old('supervisor_email', $data['supervisor_email'] ?? '') }}">
                                @error('supervisor_email')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="supervisor_password">Password</label>
                                <input id="supervisor_password" name="supervisor_password" type="password" class="auth-input setup-input-plain" autocomplete="new-password">
                                @error('supervisor_password')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="supervisor_password_confirmation">Confirm password</label>
                                <input id="supervisor_password_confirmation" name="supervisor_password_confirmation" type="password" class="auth-input setup-input-plain" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="setup-team-block">
                        <label class="setup-checkbox-row">
                            <input type="checkbox" name="create_cashier" value="1" @checked(old('create_cashier', $data['create_cashier'] ?? false))>
                            <span>Add a POS cashier account</span>
                        </label>

                        <div class="setup-form-grid">
                            <div class="auth-field">
                                <label for="cashier_name">Cashier name</label>
                                <input id="cashier_name" name="cashier_name" class="auth-input setup-input-plain" value="{{ old('cashier_name', $data['cashier_name'] ?? '') }}">
                                @error('cashier_name')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="cashier_email">Cashier email</label>
                                <input id="cashier_email" name="cashier_email" type="email" class="auth-input setup-input-plain" value="{{ old('cashier_email', $data['cashier_email'] ?? '') }}">
                                @error('cashier_email')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="cashier_password">Password</label>
                                <input id="cashier_password" name="cashier_password" type="password" class="auth-input setup-input-plain" autocomplete="new-password">
                                @error('cashier_password')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="cashier_password_confirmation">Confirm password</label>
                                <input id="cashier_password_confirmation" name="cashier_password_confirmation" type="password" class="auth-input setup-input-plain" autocomplete="new-password">
                            </div>

                            <div class="auth-field">
                                <label for="cashier_pin">POS PIN (optional)</label>
                                <input id="cashier_pin" name="cashier_pin" type="password" inputmode="numeric" maxlength="6" class="auth-input setup-input-plain auth-input--pin" value="{{ old('cashier_pin') }}">
                                @error('cashier_pin')<p class="auth-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="auth-field">
                                <label for="cashier_pin_confirmation">Confirm PIN</label>
                                <input id="cashier_pin_confirmation" name="cashier_pin_confirmation" type="password" inputmode="numeric" maxlength="6" class="auth-input setup-input-plain auth-input--pin">
                            </div>
                        </div>
                    </div>

                    <div class="setup-actions">
                        <a href="{{ route('setup.admin') }}" class="setup-btn-secondary">Back</a>
                        <button type="submit" class="auth-submit setup-btn-primary">Continue</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('setup.team.skip') }}" class="setup-skip-form">
                    @csrf
                    <button type="submit" class="setup-link-btn">Skip this step</button>
                </form>
            </div>
        </section>
    </main>
</x-setup-layout>
