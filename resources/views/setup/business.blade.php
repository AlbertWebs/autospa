<x-setup-layout title="Business Profile" :step="2">
    <main class="auth-main setup-main-single">
        <section class="auth-form-section setup-form-section-full">
            <div class="login-card setup-card setup-card-wide">
                @include('setup._progress', ['step' => 2])

                <h3>Business profile</h3>
                <p class="login-card-subtitle">Tell us about your auto spa business.</p>

                <form method="POST" action="{{ route('setup.business.store') }}" class="auth-form">
                    @csrf

                    <div class="setup-form-grid">
                        <div class="auth-field">
                            <label for="name">Business name *</label>
                            <input id="name" name="name" class="auth-input setup-input-plain" value="{{ old('name', $data['name'] ?? '') }}" required>
                            @error('name')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="legal_name">Legal name</label>
                            <input id="legal_name" name="legal_name" class="auth-input setup-input-plain" value="{{ old('legal_name', $data['legal_name'] ?? '') }}">
                            @error('legal_name')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="registration_number">Registration number</label>
                            <input id="registration_number" name="registration_number" class="auth-input setup-input-plain" value="{{ old('registration_number', $data['registration_number'] ?? '') }}">
                            @error('registration_number')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="tax_number">Tax number</label>
                            <input id="tax_number" name="tax_number" class="auth-input setup-input-plain" value="{{ old('tax_number', $data['tax_number'] ?? '') }}">
                            @error('tax_number')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field setup-field-full">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="2" class="auth-input setup-input-plain setup-textarea">{{ old('address', $data['address'] ?? '') }}</textarea>
                            @error('address')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone" type="tel" class="auth-input setup-input-plain" value="{{ old('phone', $data['phone'] ?? '') }}">
                            @error('phone')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" class="auth-input setup-input-plain" value="{{ old('email', $data['email'] ?? '') }}">
                            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field setup-field-full">
                            <label for="website">Website</label>
                            <input id="website" name="website" type="url" class="auth-input setup-input-plain" value="{{ old('website', $data['website'] ?? '') }}" placeholder="https://">
                            @error('website')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="setup-actions">
                        <a href="{{ route('setup.welcome') }}" class="setup-btn-secondary">Back</a>
                        <button type="submit" class="auth-submit setup-btn-primary">Continue</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</x-setup-layout>
