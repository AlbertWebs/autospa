@php
    $branchService = app(\App\Services\BranchService::class);
    $branches = auth()->check() ? $branchService->availableForUser(auth()->user()) : collect();
    $currentBranch = $branchService->currentBranch();
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-brand-border-subtle/80 dark:bg-brand-bg/80">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            @isset($header)
                <div class="hidden sm:block">{{ $header }}</div>
            @endisset
        </div>

        <div class="flex items-center gap-2 sm:gap-4" data-tour="header-tools">
            @if ($branches->count() > 0)
                <form method="POST" action="{{ route('branch.switch') }}" class="hidden sm:block" data-turbo="false">
                    @csrf
                    <select name="branch_id" onchange="this.form.submit()"
                        class="rounded-xl border-slate-200 bg-slate-50 text-sm dark:border-slate-700 dark:bg-slate-800">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($currentBranch?->id === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            <button type="button" @click="$store.theme.toggle()" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800" aria-label="Toggle dark mode">
                <svg x-show="!$store.theme.dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="$store.theme.dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            <button
                type="button"
                @click="$store.navMode.toggle()"
                x-bind:aria-label="$store.navMode.isMinimalist() ? 'Switch to Beast mode' : 'Switch to Minimalist mode'"
                x-bind:title="$store.navMode.isMinimalist() ? 'Switch to Beast mode' : 'Switch to Minimalist mode'"
                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
            >
                <svg x-show="!$store.navMode.isMinimalist()" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h10M4 14h16M4 18h8" />
                </svg>
                <svg x-show="$store.navMode.isMinimalist()" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                </svg>
            </button>

            <button
                type="button"
                x-show="$store.pwa.canInstall && ! $store.pwa.installed"
                x-cloak
                @click="$store.pwa.install()"
                aria-label="Install app"
                title="Install app"
                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                </svg>
            </button>

            <button
                type="button"
                x-show="$store.fullscreen.supported"
                x-cloak
                @click="$store.fullscreen.toggle()"
                x-bind:aria-label="$store.fullscreen.active ? 'Exit full screen' : 'Enter full screen'"
                x-bind:title="$store.fullscreen.active ? 'Exit full screen' : 'Enter full screen'"
                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
            >
                <svg x-show="!$store.fullscreen.active" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m16-5h-3m3 0v3M3 16v3a2 2 0 002 2h3m11-5v3a2 2 0 01-2 2h-3" />
                </svg>
                <svg x-show="$store.fullscreen.active" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9H5m0 0V5m0 4l5-5m5 5h4m0 0V5m0 4l-5-5M9 15H5m0 0v4m0-4l5 5m5-5h4m0 0v4m0-4l-5 5" />
                </svg>
            </button>

            <a href="{{ route('notifications.index') }}" class="relative rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </a>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </x-slot>
                <x-slot name="content">
                    @can('permission', 'dashboard.view')
                        <x-dropdown-link :href="route('mobile.index')">{{ __('Mobile View') }}</x-dropdown-link>
                    @endcan
                    <x-dropdown-link :href="route('manual.index')">{{ __('User Manual') }}</x-dropdown-link>
                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>
