<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">Operations</span>
    </x-slot>

    <x-ui.section-header eyebrow="Operations" />

    <div class="asp-panel max-w-6xl">
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
