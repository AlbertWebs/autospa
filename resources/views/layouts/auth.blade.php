<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In' }} | AutoSpa Pro</title>
    @vite(['resources/css/auth.css'])
</head>
<body class="auth-body">
    <header class="auth-header">
        <a href="{{ route('login') }}" class="auth-brand">
            <span class="material-symbols-outlined auth-brand-icon">directions_car</span>
            <span class="auth-brand-name">AutoSpa Pro</span>
        </a>
    </header>

  {{ $slot }}

    <footer class="auth-page-footer">
        <p>&copy; {{ date('Y') }} AutoSpa Management. All rights reserved.</p>
    </footer>
</body>
</html>
