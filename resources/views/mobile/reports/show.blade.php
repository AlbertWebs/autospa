<x-layouts.mobile :title="$title">
    <x-mobile.page-header :title="$title" :back="route('mobile.reports.index')" />

    @isset($filterRoute)
        <form method="GET" action="{{ $filterRoute }}" class="mb-4 grid grid-cols-2 gap-3">
            <x-ui.form-field label="From" for="from">
                <x-ui.input id="from" name="from" type="date" :value="$filters['from'] ?? ''" />
            </x-ui.form-field>
            <x-ui.form-field label="To" for="to">
                <x-ui.input id="to" name="to" type="date" :value="$filters['to'] ?? ''" />
            </x-ui.form-field>
            <button type="submit" class="asp-btn asp-btn-primary col-span-2 !py-2.5">Update</button>
        </form>
    @endisset

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
