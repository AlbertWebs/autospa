@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.theme-script')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-head')
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">
        @include('partials.sidebar')

        <div class="flex flex-1 flex-col lg:pl-64">
            @include('partials.header')

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <meta name="flash-success" content="{{ session('success') }}">
                @endif
                @if (session('error'))
                    <meta name="flash-error" content="{{ session('error') }}">
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-ui.toast />
    @include('partials.onboarding-tour')
    @stack('scripts')
</body>
</html>
