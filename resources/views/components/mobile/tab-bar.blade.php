@props(['tabs' => []])

@if (count($tabs) > 0)
    <nav class="asp-mobile-tabbar" aria-label="Mobile navigation">
        @foreach ($tabs as $tab)
            @php
                $isActive = request()->routeIs($tab['pattern'] ?? $tab['route']);
            @endphp
            <a href="{{ route($tab['route']) }}"
                x-show="$store.offline.online || $store.offline.isOperable('{{ $tab['route'] }}')"
                x-cloak
                @class([
                    'asp-mobile-tab',
                    'asp-mobile-tab--active' => $isActive,
                ])
                @if ($isActive) aria-current="page" @endif>
                <span class="material-symbols-outlined asp-mobile-tab-icon">{{ $tab['icon'] }}</span>
                <span class="asp-mobile-tab-label">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </nav>
@endif
