const STATIC_CACHE = 'autospa-static-v3';
const PAGES_CACHE = 'autospa-pages-v3';

const OFFLINE_FALLBACK_PATHS = ['/dashboard', '/mobile', '/pos', '/mobile/pos'];

const INSTALL_ASSETS = [
    '/logo.png',
];

function isHtmlRequest(request) {
    const accept = request.headers.get('accept') || '';

    return request.mode === 'navigate'
        || accept.includes('text/html')
        || accept.includes('application/xhtml+xml');
}

function isStaticAsset(url) {
    return url.pathname.startsWith('/build/')
        || url.pathname === '/logo.png'
        || /\.(css|js|woff2?|ttf|eot|svg|png|jpe?g|webp|ico)$/i.test(url.pathname);
}

function shouldBypassCache(url) {
    return url.pathname.startsWith('/sync/')
        || url.pathname.startsWith('/api/')
        || url.pathname.startsWith('/livewire/')
        || url.pathname.startsWith('/login')
        || url.pathname.startsWith('/logout');
}

function cacheKeysForUrl(url) {
    const parsed = new URL(url, self.location.origin);

    return [
        parsed.href,
        `${parsed.origin}${parsed.pathname}`,
    ];
}

async function matchCachedPage(request) {
    const cache = await caches.open(PAGES_CACHE);
    const keys = cacheKeysForUrl(request.url);

    for (const key of keys) {
        const match = await cache.match(key);

        if (match) {
            return match;
        }
    }

    const origin = new URL(request.url).origin;

    for (const path of OFFLINE_FALLBACK_PATHS) {
        const fallback = await cache.match(`${origin}${path}`);

        if (fallback) {
            return fallback;
        }
    }

    return null;
}

async function cachePageResponse(request, response) {
    if (!response || !response.ok || response.type === 'opaqueredirect') {
        return;
    }

    const cache = await caches.open(PAGES_CACHE);
    const clone = response.clone();

    for (const key of cacheKeysForUrl(request.url)) {
        await cache.put(key, clone.clone());
    }
}

async function precacheUrls(urls) {
    const cache = await caches.open(PAGES_CACHE);
    let cached = 0;
    let failed = 0;

    for (const rawUrl of urls) {
        try {
            const response = await fetch(rawUrl, {
                credentials: 'include',
                headers: {
                    Accept: 'text/html,application/xhtml+xml',
                    'X-Offline-Precache': '1',
                },
            });

            if (!response.ok) {
                failed += 1;
                continue;
            }

            const clone = response.clone();

            for (const key of cacheKeysForUrl(rawUrl)) {
                await cache.put(key, clone.clone());
            }

            cached += 1;
        } catch {
            failed += 1;
        }
    }

    return { cached, failed };
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(INSTALL_ASSETS))
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

self.addEventListener('message', (event) => {
    if (event.data?.type === 'PRECACHE_PAGES' && Array.isArray(event.data.urls)) {
        event.waitUntil(
            precacheUrls(event.data.urls).then((result) => {
                event.source?.postMessage({
                    type: 'PRECACHE_COMPLETE',
                    ...result,
                });
            }),
        );
    }
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
            caches.open(STATIC_CACHE).then(async (cache) => {
                const cached = await cache.match(event.request);

                if (cached) {
                    return cached;
                }

                try {
                    const response = await fetch(event.request);

                    if (response.ok) {
                        await cache.put(event.request, response.clone());
                    }

                    return response;
                } catch {
                    return cached ?? Response.error();
                }
            }),
        );

        return;
    }

    if (isHtmlRequest(event.request)) {
        event.respondWith(
            (async () => {
                const cached = await matchCachedPage(event.request);

                if (!navigator.onLine) {
                    if (cached) {
                        return cached;
                    }

                    return new Response(
                        '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Offline</title></head><body style="font-family:sans-serif;padding:2rem"><h1>You are offline</h1><p>Open AutoSpa while online once so pages can be saved for offline use.</p><p><a href="/dashboard">Try dashboard</a></p></body></html>',
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } },
                    );
                }

                try {
                    const response = await fetch(event.request);

                    await cachePageResponse(event.request, response);

                    return response;
                } catch {
                    if (cached) {
                        return cached;
                    }

                    throw new Error('Offline and no cached page available.');
                }
            })(),
        );

        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                return cached;
            }

            return fetch(event.request).catch(() => Response.error());
        }),
    );
});
