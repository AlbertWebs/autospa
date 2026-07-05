<x-setup-layout title="Administrator" :step="4">
    <main class="auth-main setup-main-single">
        <section class="auth-form-section setup-form-section-full">
            <div class="login-card setup-card setup-card-wide">
                @include('setup._progress', ['step' => 4])

                <h3>Administrator account</h3>
                <p class="login-card-subtitle">This account has full access to manage settings, users, and all branches.</p>

                <form method="POST" action="{{ route('setup.admin.store') }}" class="auth-form">
                    @csrf

                    <div class="setup-form-grid">
                        <div class="auth-field setup-field-full">
                            <label for="name">Full name *</label>
                            <input id="name" name="name" class="auth-input setup-input-plain" value="{{ old('name', $data['name'] ?? '') }}" required>
                            @error('name')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="email">Email *</label>
                            <input id="email" name="email" type="email" class="auth-input setup-input-plain" value="{{ old('email', $data['email'] ?? '') }}" required>
                            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone" type="tel" class="auth-input setup-input-plain" value="{{ old('phone', $data['phone'] ?? '') }}">
                            @error('phone')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="password">Password *</label>
                            <input id="password" name="password" type="password" class="auth-input setup-input-plain" required autocomplete="new-password">
                            @error('password')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="password_confirmation">Confirm password *</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="auth-input setup-input-plain" required autocomplete="new-password">
                        </div>
                    </div>

                    <p class="auth-field-hint">Use a strong password with at least 8 characters.</p>

                    <div class="setup-actions">
                        <a href="{{ route('setup.branch') }}" class="setup-btn-secondary">Back</a>
                        <button type="submit" class="auth-submit setup-btn-primary">Continue</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</x-setup-layout>
