<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In' }} | AutoSpa Pro</title>
    @include('partials.pwa-head')
    @vite(['resources/css/auth.css'])
</head>
<body class="auth-body">
    <header class="auth-header">
        <a href="{{ route('login') }}" class="auth-brand">
            <x-brand-logo variant="auth" />
            <span class="auth-brand-name">AutoSpa Pro</span>
        </a>

        <nav class="auth-nav" aria-label="Authentication">
            <a href="{{ url('/') }}" class="auth-nav-link">
                <span class="material-symbols-outlined">language</span>
                <span>Website</span>
            </a>
            <a href="{{ route('login') }}" class="auth-nav-link auth-nav-link-primary">
                <span class="material-symbols-outlined">login</span>
                <span>Sign In</span>
            </a>
        </nav>
    </header>

  {{ $slot }}

    <footer class="auth-page-footer">
        <p>&copy; {{ date('Y') }} AutoSpa Management. All rights reserved.</p>
    </footer>

    <script>
        // Loading spinner on every auth form submit.
        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('[type="submit"]');

                if (button) {
                    button.classList.add('is-loading');
                    button.setAttribute('aria-busy', 'true');
                }
            });
        });
    </script>
</body>
</html>
