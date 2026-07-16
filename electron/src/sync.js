import { session } from 'electron';
import {
    DESKTOP_CLIENT_HEADER,
    DESKTOP_CLIENT_VALUE,
    REMOTE_SYNC_URL,
} from '../config.js';
import {
    applyBootstrap,
    getMeta,
    listPendingMutations,
    pendingCount,
    removeMutations,
    saveIdMap,
    setMeta,
} from './db.js';

function syncUrl(path) {
    return `${REMOTE_SYNC_URL.replace(/\/$/, '')}/desktop/sync${path}`;
}

function syncHeaders(extra = {}) {
    return {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        [DESKTOP_CLIENT_HEADER]: DESKTOP_CLIENT_VALUE,
        ...extra,
    };
}

async function syncFetch(path, options = {}) {
    return session.defaultSession.fetch(syncUrl(path), {
        ...options,
        headers: {
            ...syncHeaders(options.headers),
        },
    });
}

export async function checkRemoteSession() {
    try {
        const response = await syncFetch('/ping');

        if (response.status === 200) {
            const data = await response.json();
            if (data.branch_id != null) {
                setMeta('branch_id', data.branch_id);
            }
            if (data.user_id != null) {
                setMeta('user_id', data.user_id);
            }
            setMeta('cloud_status', 'connected');

            return { ok: true, ...data };
        }

        if (response.status === 401 || response.status === 403 || response.status === 419) {
            setMeta('cloud_status', 'needs_sign_in');

            return { ok: false, reason: 'needs_sign_in' };
        }

        setMeta('cloud_status', 'unreachable');

        return { ok: false, reason: 'unreachable', status: response.status };
    } catch {
        setMeta('cloud_status', 'offline');

        return { ok: false, reason: 'offline' };
    }
}

export async function isRemoteReachable() {
    try {
        const response = await session.defaultSession.fetch(`${REMOTE_SYNC_URL.replace(/\/$/, '')}/up`, {
            method: 'GET',
        });

        return response.status >= 200 && response.status < 500;
    } catch {
        return false;
    }
}

export async function pullBootstrap() {
    const sessionCheck = await checkRemoteSession();

    if (!sessionCheck.ok) {
        return {
            ok: false,
            reason: sessionCheck.reason,
            pending: pendingCount(),
        };
    }

    const response = await syncFetch('/bootstrap');

    if (!response.ok) {
        const message = (await response.json().catch(() => ({}))).message || 'Bootstrap failed.';

        return { ok: false, reason: 'bootstrap_failed', message };
    }

    const data = await response.json();
    applyBootstrap(data);

    return {
        ok: true,
        synced_at: data.synced_at,
        pending: pendingCount(),
        branch_id: data.branch_id,
    };
}

function persistIdMapsFromResult(result) {
    if (result.customer?.uuid && result.customer?.id) {
        saveIdMap(`client:${result.customer.uuid}`, result.customer.id, 'customer');
    }

    if (result.vehicle?.uuid && result.vehicle?.id) {
        saveIdMap(`client:${result.vehicle.uuid}`, result.vehicle.id, 'vehicle');
    }

    if (result.job_card?.uuid && result.job_card?.id) {
        saveIdMap(`client:${result.job_card.uuid}`, result.job_card.id, 'job_card');
    }
}

let syncing = false;

export async function pushPendingMutations() {
    if (syncing) {
        return { synced: 0, failed: 0, skipped: true };
    }

    const sessionCheck = await checkRemoteSession();

    if (!sessionCheck.ok) {
        return {
            synced: 0,
            failed: 0,
            reason: sessionCheck.reason,
            pending: pendingCount(),
        };
    }

    const pending = listPendingMutations();

    if (pending.length === 0) {
        return { synced: 0, failed: 0, pending: 0 };
    }

    syncing = true;

    try {
        const response = await syncFetch('/push', {
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
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            return {
                synced: 0,
                failed: pending.length,
                reason: data.message || 'Push failed',
                pending: pendingCount(),
            };
        }

        const appliedIds = [];
        let failed = 0;

        for (const result of data.results ?? []) {
            if (result.status === 'applied' || result.status === 'duplicate') {
                appliedIds.push(result.id);
                persistIdMapsFromResult(result);
            } else {
                failed += 1;
            }
        }

        removeMutations(appliedIds);
        setMeta('last_sync_at', new Date().toISOString());

        return {
            synced: appliedIds.length,
            failed,
            pending: pendingCount(),
        };
    } finally {
        syncing = false;
    }
}

export async function syncNow() {
    const push = await pushPendingMutations();

    if (push.reason === 'needs_sign_in' || push.reason === 'offline' || push.reason === 'unreachable') {
        return {
            ...push,
            bootstrap: null,
            status: getMeta('cloud_status', 'offline'),
        };
    }

    const bootstrap = await pullBootstrap();

    return {
        ...push,
        bootstrap,
        status: getMeta('cloud_status', 'offline'),
        pending: pendingCount(),
    };
}

export function cloudStatusSnapshot() {
    return {
        status: getMeta('cloud_status', 'unknown'),
        pending: pendingCount(),
        bootstrap_synced_at: getMeta('bootstrap_synced_at'),
        last_sync_at: getMeta('last_sync_at'),
        branch_id: getMeta('branch_id'),
        remote_url: REMOTE_SYNC_URL,
    };
}
