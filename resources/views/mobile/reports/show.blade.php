<x-layouts.mobile :title="$title">
    <x-mobile.page-header :title="$title" :back="route('mobile.reports.index')" />
    <div class="asp-mobile-card space-y-3">
        @foreach ($data as $key => $value)
            @if (is_array($value))
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">{{ str_replace('_', ' ', $key) }}</p>
                    <p class="text-sm">{{ json_encode($value) }}</p>
                </div>
            @else
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                    <span class="font-semibold text-right">
                        @if (is_numeric($value) && str_contains($key, 'revenue'))
                            KES {{ number_format((float) $value, 0) }}
                        @else
                            {{ $value }}
                        @endif
                    </span>
                </div>
            @endif
        @endforeach
    </div>
</x-layouts.mobile>
