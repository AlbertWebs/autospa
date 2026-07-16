import {
    clientRef,
    enqueueMutation,
    getBootstrap,
    listPendingMutations,
    newUuid,
    pendingCount,
    removeMutations,
    saveBootstrap,
    saveIdMapEntry,
} from './db';
import { isOnline } from './connectivity';

let syncing = false;

function isElectronDesktop() {
    return window.autoSpaDesktop?.runtime === 'electron'
        || document.querySelector('meta[name="app-runtime"]')?.content === 'electron';
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function remoteSyncBaseUrl() {
    const remote = document.querySelector('meta[name="desktop-remote-sync-url"]')?.content?.trim()
        || window.autoSpaDesktop?.remoteSyncUrl?.trim?.()
        || '';

    return remote ? remote.replace(/\/$/, '') : '';
}

function syncApiPrefix() {
    if (isElectronDesktop() || remoteSyncBaseUrl()) {
        return '/desktop/sync';
    }

    return '/sync';
}

function syncUrl(path) {
    const base = remoteSyncBaseUrl();
    const prefix = syncApiPrefix();

    return base ? `${base}${prefix}${path}` : `${prefix}${path}`;
}

function syncHeaders() {
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    if (isElectronDesktop()) {
        headers['X-AutoSpa-Client'] = 'electron';
    }

    if (!remoteSyncBaseUrl()) {
        headers['X-CSRF-TOKEN'] = csrfToken();
    }

    return headers;
}

function syncFetchOptions(options = {}) {
    const fetchOptions = {
        ...options,
        headers: {
            ...syncHeaders(),
            ...(options.headers ?? {}),
        },
    };

    if (remoteSyncBaseUrl()) {
        fetchOptions.credentials = 'include';
    }

    return fetchOptions;
}

export async function pullBootstrap() {
    if (!isOnline()) {
        return getBootstrap();
    }

    const response = await fetch(syncUrl('/bootstrap'), syncFetchOptions());

    if (!response.ok) {
        throw new Error('Could not download offline data.');
    }

    const data = await response.json();
    await saveBootstrap(data);

    return data;
}

async function persistIdMapsFromResult(result) {
    if (result.customer?.uuid && result.customer?.id) {
        await saveIdMapEntry(clientRef(result.customer.uuid), result.customer.id, 'customer');
    }

    if (result.vehicle?.uuid && result.vehicle?.id) {
        await saveIdMapEntry(clientRef(result.vehicle.uuid), result.vehicle.id, 'vehicle');
    }

    if (result.job_card?.uuid && result.job_card?.id) {
        await saveIdMapEntry(clientRef(result.job_card.uuid), result.job_card.id, 'job_card');
    }
}

export async function syncPendingMutations() {
    if (!isOnline() || syncing) {
        return { synced: 0, failed: 0 };
    }

    const pending = await listPendingMutations();

    if (pending.length === 0) {
        return { synced: 0, failed: 0 };
    }

    syncing = true;

    try {
        const response = await fetch(syncUrl('/push'), syncFetchOptions({
            method: 'POST',
            body: JSON.stringify({
                mutations: pending.map((entry) => ({
                    id: entry.id,
                    type: entry.type,
                    client_entity_uuid: entry.client_entity_uuid,
                    payload: entry.payload,
                    created_at: entry.created_at,
                })),
            }),
        }));

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message ?? 'Sync failed.');
        }

        const appliedIds = [];
        let failed = 0;

        for (const result of data.results ?? []) {
            if (result.status === 'applied' || result.status === 'duplicate') {
                appliedIds.push(result.id);
                await persistIdMapsFromResult(result);
            } else if (result.status === 'failed') {
                failed += 1;
            }
        }

        if (appliedIds.length > 0) {
            await removeMutations(appliedIds);
        }

        if (appliedIds.length > 0) {
            await pullBootstrap();
        }

        return { synced: appliedIds.length, failed };
    } finally {
        syncing = false;
    }
}

export async function getPendingMutationCount() {
    return pendingCount();
}

export { enqueueMutation, getBootstrap, isOnline, newUuid, clientRef };

export function isSyncing() {
    return syncing;
}
