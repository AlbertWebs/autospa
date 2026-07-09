const STATIC_CACHE = 'autospa-static-v2';
const PAGES_CACHE = 'autospa-pages-v2';

const PRECACHE_URLS = [
    '/mobile',
    '/dashboard',
    '/logo.png',
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
        || url.pathname.startsWith('/api/')
        || url.pathname.startsWith('/livewire/');
}

function cacheKeyForRequest(request) {
    const url = new URL(request.url);

    return `${url.origin}${url.pathname}`;
}

async function matchCachedPage(request) {
    const cache = await caches.open(PAGES_CACHE);
    const exact = await cache.match(request);

    if (exact) {
        return exact;
    }

    const url = new URL(request.url);
    const pathOnly = await cache.match(`${url.origin}${url.pathname}`);

    if (pathOnly) {
        return pathOnly;
    }

    const fallbacks = ['/dashboard', '/mobile'];

    for (const path of fallbacks) {
        const fallback = await cache.match(`${url.origin}${path}`);

        if (fallback) {
            return fallback;
        }
    }

    return null;
}

async function cachePageResponse(request, response) {
    if (!response || !response.ok) {
        return;
    }

    const cache = await caches.open(PAGES_CACHE);
    const clone = response.clone();
    const url = new URL(request.url);

    await cache.put(request, clone);
    await cache.put(`${url.origin}${url.pathname}`, clone.clone());
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== STATIC_CACHE && key !== PAGES_CACHE)
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
            caches.open(STATIC_CACHE).then((cache) => cache.match(event.request).then((cached) => {
                if (cached) {
                    return cached;
                }

                return fetch(event.request).then((response) => {
                    if (response.ok) {
                        cache.put(event.request, response.clone());
                    }

                    return response;
                });
            })),
        );

        return;
    }

    if (isNavigationRequest(event.request)) {
        event.respondWith(
            fetch(event.request)
                .then(async (response) => {
                    await cachePageResponse(event.request, response);

                    return response;
                })
                .catch(() => matchCachedPage(event.request)),
        );

        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                return cached;
            }

            return fetch(event.request).then((response) => {
                if (response.ok && isStaticAsset(url)) {
                    caches.open(STATIC_CACHE).then((cache) => cache.put(event.request, response.clone()));
                }

                return response;
            });
        }),
    );
});
