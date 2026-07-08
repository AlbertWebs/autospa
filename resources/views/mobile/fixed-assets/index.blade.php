<x-layouts.mobile title="Fixed Assets">
    <x-mobile.page-header title="Fixed Assets" :back="route('mobile.menu')" />

    <div class="asp-mobile-list">
        @forelse ($assets as $asset)
            <div class="asp-mobile-card text-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $asset->name }}</p>
                        <p class="text-xs text-slate-500">{{ $asset->asset_tag }} · {{ $asset->category?->label() }}</p>
                    </div>
                    <x-ui.badge :color="$asset->status?->badgeColor() ?? 'slate'">{{ $asset->status?->label() }}</x-ui.badge>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span>KES {{ number_format($asset->purchase_cost, 0) }}</span>
                    @if ($asset->location)
                        <span>{{ $asset->location }}</span>
                    @endif
                    @if ($asset->assignee)
                        <span>{{ $asset->assignee->full_name }}</span>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="No fixed assets" description="Company assets will appear here." />
        @endforelse
    </div>

    <div class="mt-4">{{ $assets->links() }}</div>
</x-layouts.mobile>
