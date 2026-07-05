<x-setup-layout title="Welcome" :step="1">
    <main class="auth-main">
        <section class="auth-hero" aria-hidden="true">
            <img class="auth-hero-image" src="https://images.unsplash.com/photo-1601362840469-51e4d8d58785?auto=format&fit=crop&w=1600&q=80" alt="">
            <div class="auth-hero-overlay"></div>
            <div class="auth-hero-content">
                <span class="auth-hero-tag">Getting started</span>
                <h2 class="auth-hero-title">Welcome to<br><span>AutoSpa Pro</span></h2>
                <p class="auth-hero-text">This wizard will configure your business profile, first branch, and administrator account so you can start operations.</p>
            </div>
        </section>

        <section class="auth-form-section">
            <div class="login-card setup-card">
                @include('setup._progress', ['step' => 1])

                <h3>System requirements</h3>
                <p class="login-card-subtitle">Confirm your server is ready before continuing.</p>

                @if (session('error'))
                    <div class="auth-status" style="background: rgba(255,180,171,0.1); color: var(--auth-error); border-color: rgba(255,180,171,0.2);">
                        {{ session('error') }}
                    </div>
                @endif

                <ul class="setup-requirements">
                    @foreach ($requirements as $key => $requirement)
                        <li class="setup-requirement {{ $requirement['passed'] ? 'is-passed' : 'is-failed' }}">
                            <span class="material-symbols-outlined setup-requirement-icon">
                                {{ $requirement['passed'] ? 'check_circle' : 'error' }}
                            </span>
                            <div>
                                <p class="setup-requirement-label">{{ $requirement['label'] }}</p>
                                @if (! $requirement['passed'] && ! empty($requirement['message']))
                                    <p class="setup-requirement-message">{{ $requirement['message'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('setup.welcome.store') }}" class="auth-form">
                    @csrf
                    <button type="submit" class="auth-submit" @disabled(! $requirementsMet)>
                        {{ $requirementsMet ? 'Continue setup' : 'Resolve requirements first' }}
                    </button>
                </form>
            </div>
        </section>
    </main>
</x-setup-layout>
