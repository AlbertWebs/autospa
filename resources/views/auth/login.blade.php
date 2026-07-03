<x-auth-layout title="Sign In">
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
                <span class="auth-hero-tag">Engineered Excellence</span>
                <h2 class="auth-hero-title">
                    Precision Auto<br><span>Management Suite</span>
                </h2>
                <p class="auth-hero-text">
                    Access high-performance tools for premium automotive detailing studios.
                    Manage bays, inventory, and detailing workflows with surgical precision.
                </p>
            </div>
        </section>

        {{-- Login form --}}
        <section class="auth-form-section">
            <div class="auth-mobile-bg" aria-hidden="true">
                <img
                    src="https://images.unsplash.com/photo-1601362840469-51e4d8d58785?auto=format&fit=crop&w=800&q=80"
                    alt=""
                >
                <div class="auth-mobile-bg-overlay"></div>
            </div>

            <div class="login-card">
                <h3>Welcome Back</h3>
                <p class="login-card-subtitle">Sign in to your technician cockpit</p>

                @if (session('status'))
                    <div class="auth-status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-form">
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
                                placeholder="admin@autospa.test"
                                class="auth-input @error('email') is-invalid @enderror"
                            >
                        </div>
                        @error('email')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <div class="auth-field-header">
                            <label for="password">Password</label>
                            @if (Route::has('password.request'))
                                <a class="auth-link" href="{{ route('password.request') }}">Forgot Password?</a>
                            @endif
                        </div>
                        <div class="auth-input-wrap">
                            <span class="material-symbols-outlined auth-input-icon">lock</span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="auth-input @error('password') is-invalid @enderror"
                            >
                        </div>
                        @error('password')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="auth-remember">
                        <input type="checkbox" name="remember" id="remember_me">
                        Remember me
                    </label>

                    <button type="submit" class="auth-submit">Login to System</button>
                </form>

                <p class="auth-footer-note">
                    Staff accounts are created by your administrator.
                </p>
            </div>
        </section>
    </main>
</x-auth-layout>
