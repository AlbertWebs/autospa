@php
    $items = $items ?? [];
    $variant = $variant ?? 'sidebar';
@endphp

@if ($items === [])
    <p class="px-3 text-sm text-slate-400">No offline tools available for your role.</p>
@elseif ($variant === 'mobile')
    <div class="grid grid-cols-2 gap-3">
        @foreach ($items as $item)
            <a href="{{ $item['url'] }}" class="asp-mobile-menu-tile">
                <span class="material-symbols-outlined asp-mobile-menu-tile-icon">{{ match ($item['icon']) {
                    'shopping-cart' => 'point_of_sale',
                    'sparkles' => 'auto_awesome',
                    'clipboard' => 'assignment',
                    'truck' => 'garage',
                    default => 'offline_bolt',
                } }}</span>
                <span class="asp-mobile-menu-tile-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
@else
    <ul class="space-y-0.5">
        <li class="px-3 pb-1 pt-2">
            <span class="text-[10px] font-semibold uppercase tracking-widest text-indigo-300/80">Offline mode</span>
        </li>
        @foreach ($items as $item)
            <li>
                <a
                    href="{{ $item['url'] }}"
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
                        <x-ui.nav-icon :name="$item['icon']" class="h-4 w-4" />
                    </span>
                    <span class="truncate">{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
