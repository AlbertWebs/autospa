@props(['sections' => []])

<div class="space-y-8">
    @foreach ($sections as $sectionName => $items)
        <section>
            <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">{{ $sectionName }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($items as $item)
                    <a href="{{ $item['url'] }}" class="asp-mobile-menu-tile">
                        <span class="material-symbols-outlined asp-mobile-menu-tile-icon">{{ $item['icon'] }}</span>
                        <span class="asp-mobile-menu-tile-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
