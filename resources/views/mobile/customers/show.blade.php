<x-layouts.mobile :title="$customer->full_name">
    <x-mobile.page-header
        :title="$customer->full_name"
        :subtitle="$customer->phone"
        :back="route('mobile.customers.index')"
    />

    <div class="space-y-4">
        <div class="asp-mobile-card space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Email</span><span>{{ $customer->email ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Loyalty points</span><span>{{ $customer->loyalty_points ?? 0 }}</span></div>
        </div>

        @if ($customer->vehicles->isNotEmpty())
            <div class="asp-mobile-card">
                <h3 class="mb-2 font-semibold">Vehicles</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($customer->vehicles as $vehicle)
                        <li>
                            <a href="{{ route('mobile.vehicles.show', $vehicle) }}" class="font-medium text-brand-primary">
                                {{ $vehicle->registration_number }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('customers.show', $customer) }}" class="asp-mobile-action-btn inline-flex w-full justify-center">Full profile</a>
    </div>
</x-layouts.mobile>
