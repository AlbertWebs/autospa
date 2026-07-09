import { isOnline } from './connectivity';

export async function precachePages(urls, { concurrency = 4 } = {}) {
    if (!isOnline() || !Array.isArray(urls) || urls.length === 0) {
        return { cached: 0, failed: 0, skipped: urls?.length ?? 0 };
    }

    const queue = [...urls];
    let cached = 0;
    let failed = 0;

    async function worker() {
        while (queue.length > 0) {
            const url = queue.shift();

            try {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'text/html,application/xhtml+xml',
                        'X-Offline-Precache': '1',
                    },
                });

                if (response.ok) {
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
        { length: Math.min(concurrency, urls.length) },
        () => worker(),
    );

    await Promise.all(workers);

    return { cached, failed, skipped: 0 };
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
