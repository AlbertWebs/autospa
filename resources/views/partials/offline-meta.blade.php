@auth
    @php
        $offlineOperableMenu = \App\Support\OfflineRoutes::operableMenuForUser(auth()->user());
        $offlinePriorityUrls = array_column($offlineOperableMenu, 'url');
    @endphp
    <meta name="offline-operable-routes" content="{{ json_encode(\App\Support\OfflineRoutes::operableRouteNames()) }}">
    <meta name="offline-priority-urls" content="{{ json_encode($offlinePriorityUrls) }}">
    <meta name="offline-precache-urls" content="{{ json_encode(\App\Support\OfflineRoutes::urlsForUser(auth()->user())) }}">
@endauth
