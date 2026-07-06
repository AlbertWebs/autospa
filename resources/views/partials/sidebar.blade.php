@php
    $branchService = app(\App\Services\BranchService::class);
    $currentBranch = auth()->check() ? $branchService->currentBranch() : null;
    $user = auth()->user();
    $visibleNavigation = [];
    $pendingSection = null;
    $hasAccess = static function (array|string|null $permissions) use ($user): bool {
        if ($permissions === null) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->hasAnyPermission((array) $permissions);
    };

    foreach (config('navigation') as $item) {
        if (isset($item['section'])) {
            $pendingSection = $item;

            continue;
        }

        if (isset($item['children'])) {
            $item['children'] = array_values(array_filter(
                $item['children'],
                fn (array $child) => $hasAccess($child['permission'] ?? null)
            ));

            if ($item['children'] === []) {
                continue;
            }
        } elseif (! $hasAccess($item['permission'] ?? null)) {
            continue;
        }

        if ($pendingSection) {
            $visibleNavigation[] = $pendingSection;
            $pendingSection = null;
        }

        $visibleNavigation[] = $item;
    }

    $navigation = $visibleNavigation;
@endphp

<aside data-tour="sidebar" class="sidebar fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col border-r border-slate-800/80 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 shadow-xl shadow-black/20 transition-transform duration-300 ease-out lg:translate-x-0"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-800/80 px-5">
        <x-brand-logo size="md" />
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-white">AutoSpa</p>
            <p class="truncate text-xs text-slate-400">{{ $currentBranch?->name ?? 'Management System' }}</p>
        </div>
    </div>

    {{-- Search + Nav --}}
    <div class="flex min-h-0 flex-1 flex-col px-4 pt-4" x-data="{ query: '' }">
        <div class="relative shrink-0">
            <x-ui.nav-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
            <input
                type="search"
                x-model="query"
                placeholder="Search menu…"
                class="w-full rounded-xl border border-slate-700/80 bg-slate-800/50 py-2 pl-9 pr-3 text-sm text-slate-200 placeholder-slate-500 transition focus:border-indigo-500/50 focus:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                @keydown.escape="query = ''"
            />
        </div>
        <p x-show="$store.navMode.isMinimalist()" x-cloak class="mt-2 px-1 text-[10px] font-semibold uppercase tracking-widest text-indigo-400/80">
            Minimalist
        </p>

        <nav class="sidebar-nav mt-3 min-h-0 flex-1 overflow-y-auto pb-2">
            <ul class="space-y-0.5">
                @foreach ($navigation as $index => $item)
                    @if (isset($item['section']))
                        @php
                            $isMinimalist = $item['minimalist'] ?? false;
                            $minimalistOnly = $item['minimalist_only'] ?? false;
                        @endphp
                        <li class="px-3 pb-1 pt-4 first:pt-1" x-show="$store.navMode.visible({{ $isMinimalist ? 'true' : 'false' }}, {{ $minimalistOnly ? 'true' : 'false' }}) && query === ''">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ $item['section'] }}</span>
                        </li>
                    @elseif (isset($item['children']))
                        @php
                            $isGroupActive = collect($item['children'])->contains(fn ($c) => request()->routeIs($c['route'].'*'));
                            $hasMinimalistChild = collect($item['children'])->contains(fn ($c) => ($c['minimalist'] ?? false));
                            $groupMinimalist = ($item['minimalist'] ?? false) || $hasMinimalistChild;
                            $groupMinimalistOnly = $item['minimalist_only'] ?? false;
                        @endphp
                        <li
                            data-nav-item
                            x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }"
                            x-show="$store.navMode.visible({{ $groupMinimalist ? 'true' : 'false' }}, {{ $groupMinimalistOnly ? 'true' : 'false' }}) && (query === '' || '{{ strtolower($item['label']) }}'.includes(query.toLowerCase()) || {{ json_encode(collect($item['children'])->pluck('label')->map(fn ($l) => strtolower($l))->values()) }}.some(l => l.includes(query.toLowerCase())))"
                        >
                            <button
                                type="button"
                                @click="open = !open"
                                @class([
                                    'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
                                    'bg-slate-800/80 text-white shadow-sm ring-1 ring-slate-700/50' => $isGroupActive,
                                    'text-slate-300 hover:bg-slate-800/60 hover:text-white' => ! $isGroupActive,
                                ])
                            >
                                <span @class([
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors',
                                    'bg-indigo-500/20 text-indigo-300' => $isGroupActive,
                                    'bg-slate-800 text-slate-400 group-hover:bg-slate-700 group-hover:text-slate-200' => ! $isGroupActive,
                                ])>
                                    <x-ui.nav-icon :name="$item['icon'] ?? 'home'" class="h-4 w-4" />
                                </span>
                                <span class="flex-1 truncate text-left">{{ $item['label'] }}</span>
                                <svg class="h-4 w-4 shrink-0 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <ul
                                x-show="open"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="mt-1 space-y-0.5 pl-[3.25rem] pr-1"
                                x-cloak
                            >
                                @foreach ($item['children'] as $child)
                                    @php
                                        $childMinimalist = $child['minimalist'] ?? false;
                                        $childMinimalistOnly = $child['minimalist_only'] ?? false;
                                    @endphp
                                    <li x-show="$store.navMode.visible({{ $childMinimalist ? 'true' : 'false' }}, {{ $childMinimalistOnly ? 'true' : 'false' }}) && (query === '' || '{{ strtolower($child['label']) }}'.includes(query.toLowerCase()))">
                                        <a
                                            href="{{ Route::has($child['route']) ? route($child['route']) : '#' }}"
                                            @class([
                                                'relative block rounded-lg px-3 py-2 text-sm transition-all duration-150',
                                                'font-medium text-indigo-300 before:absolute before:-left-3 before:top-1/2 before:h-1.5 before:w-1.5 before:-translate-y-1/2 before:rounded-full before:bg-indigo-400' => request()->routeIs($child['route'].'*'),
                                                'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' => ! request()->routeIs($child['route'].'*'),
                                            ])
                                        >
                                            {{ $child['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        @php
                            $isMinimalist = $item['minimalist'] ?? false;
                            $minimalistOnly = $item['minimalist_only'] ?? false;
                        @endphp
                        <li
                            data-nav-item
                            x-show="$store.navMode.visible({{ $isMinimalist ? 'true' : 'false' }}, {{ $minimalistOnly ? 'true' : 'false' }}) && (query === '' || '{{ strtolower($item['label']) }}'.includes(query.toLowerCase()))"
                            @if (! empty($item['tour'])) data-tour="{{ $item['tour'] }}" @endif
                        >
                            <a
                                href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                                @class([
                                    'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
                                    'bg-indigo-500/15 text-indigo-200 shadow-sm ring-1 ring-indigo-500/25' => request()->routeIs($item['route'].'*'),
                                    'text-slate-300 hover:bg-slate-800/60 hover:text-white' => ! request()->routeIs($item['route'].'*'),
                                ])
                            >
                                <span @class([
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors',
                                    'bg-indigo-500/25 text-indigo-300' => request()->routeIs($item['route'].'*'),
                                    'bg-slate-800 text-slate-400 group-hover:bg-slate-700 group-hover:text-slate-200' => ! request()->routeIs($item['route'].'*'),
                                ])>
                                    <x-ui.nav-icon :name="$item['icon'] ?? 'home'" class="h-4 w-4" />
                                </span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>

    {{-- User + Logout --}}
    @auth
        <div class="shrink-0 border-t border-slate-800/80 bg-slate-900/50 p-3">
            <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                @csrf
                <button
                    type="submit"
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition-all duration-150 hover:bg-red-500/10 hover:text-red-400"
                >
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-slate-500 transition group-hover:bg-red-500/15 group-hover:text-red-400">
                        <x-ui.nav-icon name="logout" class="h-4 w-4" />
                    </span>
                    Log Out
                </button>
            </form>
        </div>
    @endauth
</aside>

<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
    x-cloak
></div>
