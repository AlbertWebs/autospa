const CACHE_VERSION = 'autospa-offline-v1';

const PRECACHE_URLS = [
    '/mobile',
    '/logo.png',
    '/build/assets/app-DbjIAjbJ.css',
    '/build/assets/app-CwuCtBX8.js',
    '/build/assets/app-D4-jI-EM.css',
];

function isNavigationRequest(request) {
    return request.mode === 'navigate'
        || (request.headers.get('accept') || '').includes('text/html');
}

function isStaticAsset(url) {
    return url.pathname.startsWith('/build/')
        || url.pathname === '/logo.png';
}

function shouldBypassCache(url) {
    return url.pathname.startsWith('/sync/')
        || url.pathname.startsWith('/api/');
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== CACHE_VERSION)
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    if (url.origin !== self.location.origin || shouldBypassCache(url)) {
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) {
                    return cached;
                }

                return fetch(event.request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, clone));
                    }

                    return response;
                });
            }),
        );

        return;
    }

    if (isNavigationRequest(event.request)) {
        event.respondWith(
            fetch(event.request)
                .then((response) => response)
                .catch(() => caches.match(event.request).then((cached) => cached || caches.match('/mobile'))),
        );

        return;
    }

    event.respondWith(fetch(event.request));
});
