<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.theme-script')
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Access Denied</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <main class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="font-mono text-[11px] font-semibold uppercase tracking-[0.28em] text-rose-500">Error 403</p>
                <h1 class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">Access denied</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    {{ trim($exception->getMessage()) !== '' ? $exception->getMessage() : 'You do not have permission to view this page.' }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Go Back
                    </a>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                        <span class="material-symbols-outlined text-base">dashboard</span>
                        Dashboard
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>
