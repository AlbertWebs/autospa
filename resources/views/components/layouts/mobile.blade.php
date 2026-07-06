@props(['title' => null, 'hideTabBar' => false])

@php
    $branchService = app(\App\Services\BranchService::class);
    $branches = auth()->check() ? $branchService->availableForUser(auth()->user()) : collect();
    $currentBranch = $branchService->currentBranch();
    $mobileNav = app(\App\Support\MobileNavigation::class);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.theme-script')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-head')
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="asp-mobile-shell">
        <header class="asp-mobile-header">
            <div class="asp-mobile-header-inner">
                <div class="asp-mobile-brand">
                    @include('partials.brand-logo', ['size' => 'sm'])
                    <span class="asp-mobile-brand-name">AutoSpa Pro</span>
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    @if ($branches->count() > 1)
                        <form method="POST" action="{{ route('branch.switch') }}" data-turbo="false">
                            @csrf
                            <select name="branch_id" onchange="this.form.submit()"
                                class="max-w-[7rem] rounded-lg border-slate-200 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected($currentBranch?->id === $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <button type="button" @click="$store.theme.toggle()" class="asp-mobile-icon-btn" aria-label="Toggle theme">
                        <span class="material-symbols-outlined text-[20px]" x-show="!$store.theme.dark" x-cloak>dark_mode</span>
                        <span class="material-symbols-outlined text-[20px]" x-show="$store.theme.dark" x-cloak>light_mode</span>
                    </button>

                    <a href="{{ route('notifications.index') }}" class="asp-mobile-icon-btn" aria-label="Notifications">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                    </a>

                    <a href="{{ route('dashboard') }}" class="hidden lg:inline-flex asp-mobile-icon-btn" aria-label="Desktop view" title="Desktop view">
                        <span class="material-symbols-outlined text-[20px]">desktop_windows</span>
                    </a>
                </div>
            </div>
        </header>

        <main class="asp-mobile-content {{ $hideTabBar ? '' : 'asp-mobile-content--with-tabbar' }}">
            @if (session('success'))
                <meta name="flash-success" content="{{ session('success') }}">
            @endif
            @if (session('error'))
                <meta name="flash-error" content="{{ session('error') }}">
            @endif

            {{ $slot }}
        </main>

        @unless ($hideTabBar)
            <x-mobile.tab-bar :tabs="$mobileNav->tabs()" />
        @endunless
    </div>

    <x-ui.toast />
    @stack('scripts')
</body>
</html>
