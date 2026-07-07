<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Operations</span>
    </x-slot>

    <header class="asp-page-header mb-6">
        <div>
            <p class="asp-page-eyebrow">Operations</p>
            <h1 class="asp-page-title">Edit Job Card</h1>
            <p class="asp-page-subtitle">Update services, assignment, and notes for job #{{ $jobCard->id }}.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('job-cards.show', $jobCard) }}" class="asp-btn asp-btn-secondary">
                <span class="material-symbols-outlined text-lg">visibility</span>
                View job card
            </a>
            <a href="{{ route('job-cards.index') }}" class="asp-btn asp-btn-ghost">All job cards</a>
        </div>
    </header>

    <div class="max-w-7xl">
        <div class="asp-panel">
            <div class="asp-panel-body">
                <form method="POST" action="{{ route('job-cards.update', $jobCard) }}" class="asp-form">
                    @csrf
                    @method('PUT')
                    @include('job-cards._form', [
                        'jobCard' => $jobCard,
                        'splitLayout' => true,
                    ])
                    <x-ui.form-actions>
                        <button type="submit" class="asp-btn asp-btn-primary min-w-[10rem]">
                            <span class="material-symbols-outlined text-lg">save</span>
                            Save Changes
                        </button>
                        <a href="{{ route('job-cards.show', $jobCard) }}" class="asp-btn asp-btn-ghost">Cancel</a>
                    </x-ui.form-actions>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
