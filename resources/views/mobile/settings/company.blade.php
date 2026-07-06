<x-layouts.mobile title="Company">
    <x-mobile.page-header title="Company" :back="route('mobile.menu')" />
  @if ($company)
        <div class="asp-mobile-card space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Name</span><span>{{ $company->name }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Email</span><span>{{ $company->email }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Phone</span><span>{{ $company->phone }}</span></div>
        </div>
        <a href="{{ route('settings.company') }}" class="asp-mobile-action-btn mt-4 inline-flex w-full justify-center">Edit on desktop</a>
    @else
        <x-ui.empty-state title="No company profile" />
    @endif
</x-layouts.mobile>
