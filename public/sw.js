const STATIC_CACHE = 'autospa-static-v6';
const PAGES_CACHE = 'autospa-pages-v6';

const OFFLINE_FALLBACK_PATHS = [
    '/',
    '/dashboard',
    '/pos',
    '/mobile/pos',
    '/job-cards/live',
    '/mobile/job-cards/live',
    '/mobile',
];

const INSTALL_ASSETS = [
    '/logo.png',
];

function requestsForUrl(urlString) {
    const parsed = new URL(urlString, self.location.origin);

    return [
        new Request(parsed.href, { credentials: 'same-origin' }),
        new Request(`${parsed.origin}${parsed.pathname}`, { credentials: 'same-origin' }),
    ];
}

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
        || url.pathname.startsWith('/livewire/');
}

function offlineHtmlResponse(message) {
    const text = message || 'Open AutoSpa while online once so pages are saved for offline use.';

    return new Response(
        `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline — AutoSpa</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; padding: 2rem; background: #0f172a; color: #e2e8f0; }
        main { max-width: 28rem; margin: 4rem auto; }
        h1 { font-size: 1.5rem; margin-bottom: 0.75rem; }
        p { line-height: 1.5; color: #94a3b8; }
        a { color: #818cf8; }
    </style>
</head>
<body>
    <main>
        <h1>You are offline</h1>
        <p>${text}</p>
        <p><a href="/dashboard">Open dashboard</a> · <a href="/pos">Open POS</a></p>
    </main>
</body>
</html>`,
        {
            status: 200,
            headers: { 'Content-Type': 'text/html; charset=utf-8' },
        },
    );
}

async function matchExactCachedPage(request) {
    const cache = await caches.open(PAGES_CACHE);

    for (const req of requestsForUrl(request.url)) {
        const match = await cache.match(req);

        if (match) {
            return match;
        }
    }

    return null;
}

async function findOfflineFallbackPage(request) {
    const url = new URL(request.url);
    const origin = url.origin;
    const cache = await caches.open(PAGES_CACHE);

    // Never substitute a staff page for the public landing (or other exact paths).
    const paths = url.pathname === '/'
        ? ['/']
        : OFFLINE_FALLBACK_PATHS;

    for (const path of paths) {
        for (const req of requestsForUrl(`${origin}${path}`)) {
            const fallback = await cache.match(req);

            if (fallback) {
                return fallback;
            }
        }
    }

    return null;
}

async function cachePageResponse(request, response) {
    if (!response || !response.ok || response.type === 'opaqueredirect' || response.redirected) {
        return;
    }

    const requestUrl = new URL(request.url);
    const responseUrl = new URL(response.url);

    // Do not store a different page under this request path (e.g. old / → dashboard).
    if (requestUrl.origin !== responseUrl.origin || requestUrl.pathname !== responseUrl.pathname) {
        return;
    }

    const cache = await caches.open(PAGES_CACHE);
    const clone = response.clone();

    for (const req of requestsForUrl(request.url)) {
        await cache.put(req, clone.clone());
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

            for (const req of requestsForUrl(rawUrl)) {
                await cache.put(req, clone.clone());
            }

            cached += 1;
        } catch {
            failed += 1;
        }
    }

    return { cached, failed };
}

async function serveHtml(request) {
    try {
        const response = await fetch(request);

        if (response.ok) {
            await cachePageResponse(request, response);

            return response;
        }

        const exactCached = await matchExactCachedPage(request);

        if (exactCached) {
            return exactCached;
        }
    } catch {
        const exactCached = await matchExactCachedPage(request);

        if (exactCached) {
            return exactCached;
        }

        const fallback = await findOfflineFallbackPage(request);

        if (fallback) {
            return fallback;
        }
    }

    return offlineHtmlResponse();
}

async function serveStatic(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.ok) {
            await cache.put(request, response.clone());
        }

        return response;
    } catch {
        if (cached) {
            return cached;
        }

        const url = new URL(request.url);

        if (url.pathname.endsWith('.css')) {
            return new Response('/* offline */', {
                status: 200,
                headers: { 'Content-Type': 'text/css; charset=utf-8' },
            });
        }

        if (url.pathname.endsWith('.js')) {
            return new Response('/* offline */', {
                status: 200,
                headers: { 'Content-Type': 'application/javascript; charset=utf-8' },
            });
        }

        return new Response('', { status: 503 });
    }
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
        event.respondWith(serveStatic(event.request));

        return;
    }

    if (isHtmlRequest(event.request)) {
        event.respondWith(serveHtml(event.request));

        return;
    }

    event.respondWith(
        caches.match(event.request).then(async (cached) => {
            if (cached) {
                return cached;
            }

            try {
                return await fetch(event.request);
            } catch {
                return cached ?? new Response('', { status: 503 });
            }
        }),
    );
});
