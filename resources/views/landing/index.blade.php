@php
    $waPhone = preg_replace('/\D+/', '', (string) ($phone ?? ''));
    if (str_starts_with($waPhone, '0')) {
        $waPhone = '254'.substr($waPhone, 1);
    }
    $whatsappUrl = filled($waPhone) ? 'https://wa.me/'.$waPhone : null;
    $telUrl = filled($phone) ? 'tel:'.preg_replace('/\s+/', '', $phone) : null;
    $brandLogo = filled($company?->logo_path)
        ? asset('storage/'.$company->logo_path)
        : asset('brand/logo.jpeg');
    $pageTitle = $companyName.' — Auto Spa in '.$locality;
    $pageDescription = 'Book Auto Spa detailing, wash, or carpet cleaning in '.$locality.'. '.$companyName.' — spotless finish, on-time slots.';

    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'AutoWash',
        'name' => $companyName,
        'description' => 'Auto Spa wash, detailing, and carpet cleaning in '.$locality.'.',
        'url' => url('/'),
        'telephone' => $phone,
        'email' => $email,
        'image' => $brandLogo,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $address,
            'addressLocality' => $locality,
            'addressCountry' => 'KE',
        ],
        'areaServed' => [
            '@type' => 'Place',
            'name' => $locality,
        ],
        'priceRange' => '$$',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $servicesByCategory = $services->groupBy(fn ($service) => $service->category?->name ?: 'Spa packages');
@endphp

<x-layouts.landing
    :company="$company"
    :company-name="$companyName"
    :locality="$locality"
    :phone="$phone"
    :email="$email"
    :address="$address"
    :json-ld="$jsonLd"
    :title="$pageTitle"
    :description="$pageDescription"
    :og-image="$brandLogo"
>
    <div class="landing">
        <header class="landing-nav">
            <div class="landing-shell landing-nav-inner">
                <a href="{{ route('landing') }}" class="landing-brand">
                    <img src="{{ $brandLogo }}" alt="{{ $companyName }}" class="landing-logo" width="40" height="40">
                    <span>{{ $companyName }}</span>
                </a>

                <nav class="landing-nav-links" aria-label="Primary">
                    <a href="#services" class="landing-nav-link">Services</a>
                    <a href="#visit" class="landing-nav-link">Visit</a>
                    <div class="landing-nav-actions">
                        <a href="#book" class="landing-nav-btn landing-nav-btn-primary">
                            <x-landing.icon name="calendar" />
                            <span>Book now</span>
                        </a>
                        <a href="{{ route('login') }}" class="landing-nav-btn landing-nav-btn-quiet">
                            <x-landing.icon name="user" />
                            <span>Staff</span>
                        </a>
                    </div>
                </nav>
            </div>
        </header>

        <section class="landing-hero" aria-label="Welcome">
            <div class="landing-hero-media" aria-hidden="true">
                <video
                    class="landing-hero-video"
                    autoplay
                    muted
                    loop
                    playsinline
                    preload="metadata"
                    poster="{{ asset('brand/logo.jpeg') }}"
                >
                    <source src="{{ asset('video/bg.mp4') }}" type="video/mp4">
                </video>
            </div>
            <div class="landing-hero-veil" aria-hidden="true"></div>
            <div class="landing-shell landing-hero-content">
                <p class="landing-eyebrow landing-rise">Auto Spa · {{ $locality }}</p>
                <h1 class="landing-brand-hero landing-rise landing-rise-delay-1">{{ $companyName }}</h1>
                <p class="landing-lede landing-rise landing-rise-delay-2">
                    Exterior wash, interior detail, and carpet cleaning — pull in when it suits you.
                </p>
                <div class="landing-cta-row landing-rise landing-rise-delay-3">
                    <a href="#book" class="landing-btn landing-btn-primary">
                        <x-landing.icon name="calendar" />
                        <span>Book Auto Spa</span>
                    </a>
                    @if ($telUrl)
                        <a href="{{ $telUrl }}" class="landing-btn landing-btn-secondary">
                            <x-landing.icon name="phone" />
                            <span>Call us</span>
                        </a>
                    @endif
                    @if ($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" class="landing-btn landing-btn-secondary" target="_blank" rel="noopener">
                            <x-landing.icon name="whatsapp" />
                            <span>WhatsApp</span>
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <section id="services" class="landing-section">
            <div class="landing-shell">
                <header class="landing-section-head">
                    <p class="landing-eyebrow">Services</p>
                    <h2 class="landing-section-title">Spa packages</h2>
                    <p class="landing-section-copy">Pick your wash or detail, then book a slot below.</p>
                </header>

                @forelse ($servicesByCategory as $categoryName => $categoryServices)
                    <div class="landing-menu">
                        <h3 class="landing-menu-label">{{ $categoryName }}</h3>
                        <ul class="landing-menu-list">
                            @foreach ($categoryServices as $service)
                                <li class="landing-menu-item">
                                    <div class="landing-menu-copy">
                                        <p class="landing-menu-name">{{ $service->name }}</p>
                                        @if (filled($service->description))
                                            <p class="landing-menu-desc">{{ $service->description }}</p>
                                        @endif
                                    </div>
                                    <div class="landing-menu-meta">
                                        @if ($service->duration_minutes)
                                            <span>{{ $service->duration_minutes }} min</span>
                                        @endif
                                        <strong>KES {{ number_format((float) $service->price, 0) }}</strong>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="landing-empty">Packages will show here once published. Call us to book in the meantime.</p>
                @endforelse
            </div>
        </section>

        <section id="visit" class="landing-section landing-section-soft">
            <div class="landing-shell landing-visit">
                <header class="landing-section-head">
                    <p class="landing-eyebrow">Visit</p>
                    <h2 class="landing-section-title">In {{ $locality }}</h2>
                    <p class="landing-section-copy">Drive in for your slot, or message us to line one up.</p>
                </header>

                <div class="landing-visit-grid">
                    <div class="landing-visit-block">
                        @if (filled($address))
                            <p class="landing-meta-label">Address</p>
                            <p class="landing-meta-value">{{ $address }}</p>
                        @endif

                        @if (filled($phone))
                            <p class="landing-meta-label">Phone</p>
                            <p class="landing-meta-value">
                                <a href="{{ $telUrl }}">{{ $phone }}</a>
                            </p>
                        @endif

                        @if (filled($email))
                            <p class="landing-meta-label">Email</p>
                            <p class="landing-meta-value">
                                <a href="mailto:{{ $email }}">{{ $email }}</a>
                            </p>
                        @endif

                        <div class="landing-visit-actions">
                            @if ($whatsappUrl)
                                <a href="{{ $whatsappUrl }}" class="landing-btn landing-btn-ghost" target="_blank" rel="noopener">
                                    <x-landing.icon name="whatsapp" />
                                    <span>WhatsApp</span>
                                </a>
                            @endif
                            @if ($telUrl)
                                <a href="{{ $telUrl }}" class="landing-btn landing-btn-ghost">
                                    <x-landing.icon name="phone" />
                                    <span>Call</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="landing-visit-block">
                        <p class="landing-meta-label">Hours</p>
                        @if ($hours->isNotEmpty())
                            <ul class="landing-hours">
                                @foreach ($hours as $hour)
                                    <li>
                                        <span>{{ $dayNames[$hour->day_of_week] ?? 'Day '.$hour->day_of_week }}</span>
                                        <span>
                                            @if ($hour->is_closed)
                                                Closed
                                            @else
                                                {{ \Carbon\Carbon::parse($hour->open_time)->format('g:i A') }}
                                                –
                                                {{ \Carbon\Carbon::parse($hour->close_time)->format('g:i A') }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="landing-meta-value">Open daily — tell us your preferred slot when you book.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section id="book" class="landing-section">
            <div class="landing-shell landing-book">
                <header class="landing-section-head">
                    <p class="landing-eyebrow">Book</p>
                    <h2 class="landing-section-title">Book Auto Spa</h2>
                    <p class="landing-section-copy">
                        Request a slot and we’ll confirm shortly. Plate is optional if you’re dropping carpets only.
                    </p>
                </header>

                @if (session('success'))
                    <div class="landing-alert landing-alert-success" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="landing-alert landing-alert-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('landing.book') }}" class="landing-form">
                    @csrf
                    <div class="landing-honeypot" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="landing-form-grid">
                        <label class="landing-field">
                            <span>Full name</span>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required maxlength="255" autocomplete="name">
                        </label>
                        <label class="landing-field">
                            <span>Phone</span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="30" placeholder="07…" autocomplete="tel">
                        </label>
                        <label class="landing-field">
                            <span>Email <em>optional</em></span>
                            <input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email">
                        </label>
                        <label class="landing-field">
                            <span>Vehicle plate <em>optional</em></span>
                            <input type="text" name="registration_number" value="{{ old('registration_number') }}" maxlength="20" placeholder="KAA 123A">
                        </label>
                        <label class="landing-field landing-field-wide">
                            <span>Preferred date &amp; time</span>
                            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required>
                        </label>
                    </div>

                    <fieldset class="landing-fieldset">
                        <legend>Choose packages</legend>
                        <div class="landing-service-checks">
                            @forelse ($services as $service)
                                <label class="landing-check">
                                    <input
                                        type="checkbox"
                                        name="service_ids[]"
                                        value="{{ $service->id }}"
                                        @checked(collect(old('service_ids', []))->contains($service->id))
                                    >
                                    <span class="landing-check-copy">
                                        <strong>{{ $service->name }}</strong>
                                        <small>KES {{ number_format((float) $service->price, 0) }}</small>
                                    </span>
                                </label>
                            @empty
                                <p class="landing-empty">No packages listed yet. Please call to book.</p>
                            @endforelse
                        </div>
                    </fieldset>

                    <label class="landing-field">
                        <span>Notes <em>optional</em></span>
                        <textarea name="notes" rows="3" maxlength="1000" placeholder="Anything we should know?">{{ old('notes') }}</textarea>
                    </label>

                    <div class="landing-form-actions">
                        <button type="submit" class="landing-btn landing-btn-primary" @disabled($services->isEmpty())>
                            <x-landing.icon name="send" />
                            <span>Request slot</span>
                        </button>
                        <p class="landing-form-note">We’ll reply to confirm your slot.</p>
                    </div>
                </form>
            </div>
        </section>

        <footer class="landing-footer">
            <div class="landing-shell landing-footer-inner">
                <div>
                    <p class="landing-footer-brand">{{ $companyName }}</p>
                    <p class="landing-footer-tag">Auto Spa in {{ $locality }}</p>
                </div>
                <div class="landing-footer-links">
                    @if ($telUrl)
                        <a href="{{ $telUrl }}">{{ $phone }}</a>
                    @endif
                    @if ($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp</a>
                    @endif
                    <a href="{{ route('login') }}">Staff sign in</a>
                </div>
            </div>
        </footer>

        <div class="landing-dock" aria-label="Quick actions">
            <div class="landing-dock-inner">
                @if ($telUrl)
                    <a href="{{ $telUrl }}" class="landing-btn landing-btn-ghost">
                        <x-landing.icon name="phone" />
                        <span>Call</span>
                    </a>
                @elseif ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" class="landing-btn landing-btn-ghost" target="_blank" rel="noopener">
                        <x-landing.icon name="whatsapp" />
                        <span>WhatsApp</span>
                    </a>
                @endif
                <a href="#book" class="landing-btn landing-btn-primary">
                    <x-landing.icon name="calendar" />
                    <span>Book Auto Spa</span>
                </a>
            </div>
        </div>

        @if ($whatsappUrl)
            <a
                href="{{ $whatsappUrl }}"
                class="landing-wa-widget"
                target="_blank"
                rel="noopener"
                aria-label="Chat on WhatsApp"
            >
                <x-landing.icon name="whatsapp" class="landing-wa-widget-icon" />
                <span class="landing-wa-widget-label">Chat with us</span>
            </a>
        @endif
    </div>
</x-layouts.landing>
