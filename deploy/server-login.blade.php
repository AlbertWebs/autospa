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

                <div class="auth-login-tabs" role="tablist" aria-label="Sign in method">
                    <button
                        type="button"
                        class="auth-login-tab is-active"
                        data-login-tab="password"
                        role="tab"
                        aria-selected="true"
                    >
                        Password
                    </button>
                    <button
                        type="button"
                        class="auth-login-tab"
                        data-login-tab="pin"
                        role="tab"
                        aria-selected="false"
                    >
                        PIN
                    </button>
                </div>

                <form method="POST" action="{{ route('login') }}" class="auth-form" id="login-form">
                    @csrf
                    <input type="hidden" name="login_method" id="login_method" value="{{ old('login_method', 'password') }}">

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

                    <div class="auth-field" data-login-panel="password">
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
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="auth-input @error('password') is-invalid @enderror"
                            >
                        </div>
                        @error('password')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                        <p class="auth-field-hint">For Admin and Supervisor accounts.</p>
                    </div>

                    <div class="auth-field is-hidden" data-login-panel="pin">
                        <label for="pin">PIN</label>
                        <div class="auth-input-wrap">
                            <span class="material-symbols-outlined auth-input-icon">pin</span>
                            <input
                                id="pin"
                                type="password"
                                name="pin"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                autocomplete="one-time-code"
                                placeholder="4–6 digit PIN"
                                class="auth-input auth-input--pin @error('pin') is-invalid @enderror"
                            >
                        </div>
                        @error('pin')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                        <p class="auth-field-hint">Quick sign-in for staff with a PIN set by your administrator.</p>
                    </div>

                    <label class="auth-remember">
                        <input type="checkbox" name="remember" id="remember_me">
                        Remember me
                    </label>

                    <button type="submit" class="auth-submit" id="login-submit">Login to System</button>
                </form>

                <p class="auth-footer-note">
                    Staff accounts are created by your administrator.
                </p>
            </div>
        </section>
    </main>

    <script>
        (() => {
            const form = document.getElementById('login-form');
            const methodInput = document.getElementById('login_method');
            const passwordInput = document.getElementById('password');
            const pinInput = document.getElementById('pin');
            const submitButton = document.getElementById('login-submit');
            const tabs = document.querySelectorAll('[data-login-tab]');
            const panels = document.querySelectorAll('[data-login-panel]');

            const setMode = (mode) => {
                methodInput.value = mode;

                tabs.forEach((tab) => {
                    const active = tab.dataset.loginTab === mode;
                    tab.classList.toggle('is-active', active);
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    const show = panel.dataset.loginPanel === mode;
                    panel.classList.toggle('is-hidden', !show);
                });

                if (mode === 'pin') {
                    passwordInput.removeAttribute('required');
                    passwordInput.value = '';
                    pinInput.setAttribute('required', 'required');
                    submitButton.textContent = 'Sign In with PIN';
                } else {
                    pinInput.removeAttribute('required');
                    pinInput.value = '';
                    passwordInput.setAttribute('required', 'required');
                    submitButton.textContent = 'Login to System';
                }
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => setMode(tab.dataset.loginTab));
            });

            setMode(methodInput.value === 'pin' ? 'pin' : 'password');
        })();
    </script>
</x-auth-layout>
