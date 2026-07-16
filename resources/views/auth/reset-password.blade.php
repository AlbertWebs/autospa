<x-auth-layout title="Reset Password">
    <main class="auth-main">
        {{-- Hero --}}
        <section class="auth-hero" aria-hidden="true">
            <img
                class="auth-hero-image"
                src="https://images.unsplash.com/photo-1601362840469-51e4d8d58785?auto=format&fit=crop&w=1600&q=80"
                alt=""
            >
            <div class="auth-hero-overlay"></div>
            <div class="auth-hero-content">
                <span class="auth-hero-tag">Account Recovery</span>
                <h2 class="auth-hero-title">
                    Choose a new<br><span>Password</span>
                </h2>
                <p class="auth-hero-text">
                    Pick a strong password you have not used before. You will be signed in
                    right after it is saved.
                </p>
            </div>
        </section>

        {{-- Form --}}
        <section class="auth-form-section">
            <div class="auth-mobile-bg" aria-hidden="true">
                <img
                    src="https://images.unsplash.com/photo-1601362840469-51e4d8d58785?auto=format&fit=crop&w=800&q=80"
                    alt=""
                >
                <div class="auth-mobile-bg-overlay"></div>
            </div>

            <div class="login-card">
                <h3>Reset Password</h3>
                <p class="login-card-subtitle">Set a new password for your account</p>

                <form method="POST" action="{{ route('password.store') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="auth-field">
                        <label for="email">Email Address</label>
                        <div class="auth-input-wrap">
                            <span class="material-symbols-outlined auth-input-icon">mail</span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', $request->email) }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="you@example.com"
                                class="auth-input @error('email') is-invalid @enderror"
                            >
                        </div>
                        @error('email')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">New Password</label>
                        <div class="auth-input-wrap">
                            <span class="material-symbols-outlined auth-input-icon">lock</span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="auth-input @error('password') is-invalid @enderror"
                            >
                        </div>
                        @error('password')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="auth-input-wrap">
                            <span class="material-symbols-outlined auth-input-icon">lock_reset</span>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="auth-input @error('password_confirmation') is-invalid @enderror"
                            >
                        </div>
                        @error('password_confirmation')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="auth-submit">Reset Password</button>
                </form>

                <div class="auth-page-nav">
                    <a href="{{ route('login') }}" class="auth-link">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Back to Sign In
                    </a>
                </div>
            </div>
        </section>
    </main>
</x-auth-layout>
