@auth
    <meta name="offline-operable-routes" content="{{ json_encode(\App\Support\OfflineRoutes::operableRouteNames()) }}">
    <meta name="offline-precache-urls" content="{{ json_encode(\App\Support\OfflineRoutes::urlsForUser(auth()->user())) }}">
@endauth
