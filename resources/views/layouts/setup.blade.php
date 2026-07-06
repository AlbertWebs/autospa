<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Setup' }} | AutoSpa Pro</title>
    @include('partials.pwa-head')
    @vite(['resources/css/auth.css'])
</head>
<body class="auth-body">
    <header class="auth-header">
        <div class="auth-brand">
            @include('partials.brand-logo', ['size' => 'sm', 'class' => 'auth-brand-logo h-10 w-10'])
            <span class="auth-brand-name">AutoSpa Pro</span>
        </div>
        <span class="setup-header-badge">First-time setup</span>
    </header>

    {{ $slot }}

    <footer class="auth-page-footer">
        <p>&copy; {{ date('Y') }} AutoSpa Management. All rights reserved.</p>
    </footer>
</body>
</html>
