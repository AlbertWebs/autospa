<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Edit Job Card</span>
    </x-slot>

    <header class="asp-page-header">
        <div>
            <p class="asp-page-eyebrow">Operations</p>
            <h1 class="asp-page-title">Edit Job Card</h1>
            <p class="asp-page-subtitle">Update assignment, status, or notes for this job.</p>
        </div>
    </header>

    <div class="asp-panel max-w-3xl">
        <div class="asp-panel-header">
            <h2 class="asp-panel-title">Job Card Details</h2>
            <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">edit_note</span>
        </div>

        <div class="asp-panel-body">
            <form method="POST" action="{{ route('job-cards.update', $jobCard) }}" class="asp-form">
                @csrf
                @method('PUT')
                @include('job-cards._form', ['jobCard' => $jobCard])
                <x-ui.form-actions>
                    <button type="submit" class="asp-btn asp-btn-primary">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Save Changes
                    </button>
                    <a href="{{ route('job-cards.show', $jobCard) }}" class="asp-btn asp-btn-ghost">Cancel</a>
                </x-ui.form-actions>
            </form>
        </div>
    </div>
</x-layouts.app>
