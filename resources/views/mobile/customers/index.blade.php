<x-layouts.mobile title="Customers">
    <x-mobile.page-header title="Customers" subtitle="Client directory" />

    <form method="GET" class="mb-4">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search name, phone, email…" class="asp-input w-full">
    </form>

    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($customers as $customer)
            <x-mobile.list-card
                :href="route('mobile.customers.show', $customer)"
                :title="$customer->full_name"
                :subtitle="$customer->phone"
                :meta="$customer->email"
            />
        @empty
            <x-ui.empty-state title="No customers found" />
        @endforelse
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
</x-layouts.mobile>
