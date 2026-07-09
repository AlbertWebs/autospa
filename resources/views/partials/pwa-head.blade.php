@include('partials.desktop-meta')
<link rel="manifest" href="{{ route('manifest') }}">
<meta name="theme-color" content="{{ config('pwa.theme_color') }}">
<meta name="application-name" content="{{ config('pwa.short_name') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ config('pwa.short_name') }}">
<link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
@unless (config('desktop.enabled'))
<script>
    (function () {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        navigator.serviceWorker.register(@json(url('/sw.js')), { scope: '/' }).catch(function () {
            // Service worker registration is optional for local development.
        });
    })();
</script>
@endunless
