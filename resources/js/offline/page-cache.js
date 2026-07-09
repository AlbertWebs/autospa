import { isOnline } from './connectivity';

export const PAGES_CACHE = 'autospa-pages-v3';
export const STATIC_CACHE = 'autospa-static-v3';

let serviceWorkerRegistration = null;
let precacheStarted = false;

function cacheKeysForUrl(url) {
    const parsed = new URL(url, window.location.origin);

    return [
        parsed.href,
        `${parsed.origin}${parsed.pathname}`,
    ];
}

export async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    if (serviceWorkerRegistration) {
        return serviceWorkerRegistration;
    }

    try {
        serviceWorkerRegistration = await navigator.serviceWorker.register('/sw.js');
        await navigator.serviceWorker.ready;
    } catch {
        serviceWorkerRegistration = null;
    }

    return serviceWorkerRegistration;
}

async function cachePageUrl(url) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'text/html,application/xhtml+xml',
            'X-Offline-Precache': '1',
        },
    });

    if (!response.ok) {
        return false;
    }

    const cache = await caches.open(PAGES_CACHE);
    const clone = response.clone();

    for (const key of cacheKeysForUrl(url)) {
        await cache.put(key, clone.clone());
    }

    return true;
}

async function isPageCached(url) {
    const cache = await caches.open(PAGES_CACHE);

    for (const key of cacheKeysForUrl(url)) {
        const match = await cache.match(key);

        if (match) {
            return true;
        }
    }

    return false;
}

export async function precachePages(urls, { concurrency = 4 } = {}) {
    if (!isOnline() || !Array.isArray(urls) || urls.length === 0) {
        return { cached: 0, failed: 0, skipped: urls?.length ?? 0 };
    }

    await registerServiceWorker();

    const uniqueUrls = [...new Set(urls)];
    const queue = [];
    let skipped = 0;

    for (const url of uniqueUrls) {
        if (await isPageCached(url)) {
            skipped += 1;
            continue;
        }

        queue.push(url);
    }

    if (queue.length === 0) {
        return { cached: 0, failed: 0, skipped };
    }

    let cached = 0;
    let failed = 0;

    async function worker() {
        while (queue.length > 0) {
            const url = queue.shift();

            try {
                if (await cachePageUrl(url)) {
                    cached += 1;
                } else {
                    failed += 1;
                }
            } catch {
                failed += 1;
            }
        }
    }

    const workers = Array.from(
        { length: Math.min(concurrency, queue.length) },
        () => worker(),
    );

    await Promise.all(workers);

    if (navigator.serviceWorker?.controller) {
        navigator.serviceWorker.controller.postMessage({
            type: 'PRECACHE_PAGES',
            urls: uniqueUrls,
        });
    }

    return { cached, failed, skipped };
}

export async function precacheCurrentAssets() {
    if (!isOnline()) {
        return { cached: 0, failed: 0 };
    }

    const urls = new Set();

    document.querySelectorAll('link[rel="stylesheet"][href]').forEach((link) => {
        if (link.href?.startsWith(window.location.origin)) {
            urls.add(link.href);
        }
    });

    document.querySelectorAll('script[src]').forEach((script) => {
        if (script.src?.startsWith(window.location.origin)) {
            urls.add(script.src);
        }
    });

    const cache = await caches.open(STATIC_CACHE);
    let cached = 0;
    let failed = 0;

    for (const url of urls) {
        try {
            const existing = await cache.match(url);

            if (existing) {
                continue;
            }

            const response = await fetch(url);

            if (response.ok) {
                await cache.put(url, response);
                cached += 1;
            } else {
                failed += 1;
            }
        } catch {
            failed += 1;
        }
    }

    return { cached, failed };
}

export function readPrecacheUrlsFromMeta() {
    const meta = document.querySelector('meta[name="offline-precache-urls"]');

    if (!meta?.content) {
        return [];
    }

    try {
        const urls = JSON.parse(meta.content);

        return Array.isArray(urls) ? urls : [];
    } catch {
        return [];
    }
}

export async function startOfflinePrecache(extraUrls = []) {
    if (!isOnline() || precacheStarted) {
        return null;
    }

    precacheStarted = true;

    await registerServiceWorker();
    await precacheCurrentAssets();

    const urls = [...new Set([
        ...readPrecacheUrlsFromMeta(),
        ...extraUrls,
        window.location.href,
    ])];

    return precachePages(urls, { concurrency: 6 });
}

export function registerOfflineFormGuard() {
    if (window.__autospaOfflineFormGuard) {
        return;
    }

    window.__autospaOfflineFormGuard = true;

    document.addEventListener('submit', (event) => {
        if (isOnline()) {
            return;
        }

        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.offlineCapable === 'true') {
            return;
        }

        if ((form.method || 'get').toLowerCase() === 'get') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        window.Alpine?.store('toast')?.show(
            'You are offline. Use POS cash checkout, job cards, or quick customer/vehicle create — those sync when you reconnect.',
            'error',
        );
    }, true);
}
