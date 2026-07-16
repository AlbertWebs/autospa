<x-auth-layout title="Forgot Password">
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
                    Reset your<br><span>Password</span>
                </h2>
                <p class="auth-hero-text">
                    Enter the email address linked to your account and we will send you a
                    secure link to choose a new password.
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
                <h3>Forgot Password?</h3>
                <p class="login-card-subtitle">We will email you a reset link</p>

                @if (session('status'))
                    <div class="auth-status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="email">Email Address</label>
                        <div class="auth-input-wrap">
                            <span class="material-symbols-outlined auth-input-icon">mail</span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
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

                    <button type="submit" class="auth-submit">Email Reset Link</button>
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
