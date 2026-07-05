@php
    $steps = [
        1 => 'Welcome',
        2 => 'Business',
        3 => 'Branch',
        4 => 'Admin',
        5 => 'Team',
        6 => 'Preferences',
    ];
@endphp

<div class="setup-progress" aria-label="Setup progress">
    @foreach ($steps as $number => $label)
        <div class="setup-progress-step {{ $number < $step ? 'is-complete' : ($number === $step ? 'is-active' : '') }}">
            <span class="setup-progress-dot">{{ $number < $step ? '✓' : $number }}</span>
            <span class="setup-progress-label">{{ $label }}</span>
        </div>
        @if (! $loop->last)
            <div class="setup-progress-line {{ $number < $step ? 'is-complete' : '' }}"></div>
        @endif
    @endforeach
</div>
