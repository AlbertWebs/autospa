<x-layouts.mobile title="More">
    <x-mobile.page-header title="More" subtitle="All modules and settings" />

    <div x-show="$store.offline.online">
        <x-mobile.section-grid :sections="$sections" />
    </div>

    <div x-show="! $store.offline.online" x-cloak>
        @include('partials.offline-operable-nav', [
            'items' => \App\Support\OfflineRoutes::operableMenuForUser(auth()->user(), true),
            'variant' => 'mobile',
        ])
    </div>
</x-layouts.mobile>
