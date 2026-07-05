<x-setup-layout title="First Branch" :step="3">
    <main class="auth-main setup-main-single">
        <section class="auth-form-section setup-form-section-full">
            <div class="login-card setup-card setup-card-wide">
                @include('setup._progress', ['step' => 3])

                <h3>First branch</h3>
                <p class="login-card-subtitle">Create your primary location. You can add more branches later in Settings.</p>

                <form method="POST" action="{{ route('setup.branch.store') }}" class="auth-form">
                    @csrf

                    <div class="setup-form-grid">
                        <div class="auth-field">
                            <label for="name">Branch name *</label>
                            <input id="name" name="name" class="auth-input setup-input-plain" value="{{ old('name', $data['name'] ?? '') }}" required placeholder="e.g. AutoSpa Westlands">
                            @error('name')<p class="auth-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="auth-field">
                            <label for="code">Branch code *</label>
                            <input id="code" name="code" class="auth-input setup-input-plain" value="{{ old('code', $data['code'] ?? '') }}" required maxlength="20" placeholder="e.g. HQ">
                            @error('code')<p class="auth-error">{{ $message }}</p>@enderror
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
                    </div>

                    <div class="setup-actions">
                        <a href="{{ route('setup.business') }}" class="setup-btn-secondary">Back</a>
                        <button type="submit" class="auth-submit setup-btn-primary">Continue</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</x-setup-layout>
