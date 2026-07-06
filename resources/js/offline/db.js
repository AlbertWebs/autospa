import Dexie from 'dexie';

export const offlineDb = new Dexie('autospa_offline');

offlineDb.version(1).stores({
    outbox: 'id, created_at, type',
    reference_cache: 'key',
    id_map: 'client_ref',
});

export function clientRef(uuid) {
    return `client:${uuid}`;
}

export function newUuid() {
    return crypto.randomUUID();
}

export async function enqueueMutation(type, payload, clientEntityUuid = null) {
    const id = newUuid();

    await offlineDb.outbox.add({
        id,
        type,
        client_entity_uuid: clientEntityUuid ?? payload.uuid ?? newUuid(),
        payload,
        created_at: new Date().toISOString(),
    });

    return id;
}

export async function listPendingMutations() {
    return offlineDb.outbox.orderBy('created_at').toArray();
}

export async function removeMutations(ids) {
    await offlineDb.outbox.bulkDelete(ids);
}

export async function pendingCount() {
    return offlineDb.outbox.count();
}

export async function saveBootstrap(data) {
    await offlineDb.reference_cache.put({
        key: 'bootstrap',
        data,
        updated_at: new Date().toISOString(),
    });
}

export async function getBootstrap() {
    const row = await offlineDb.reference_cache.get('bootstrap');

    return row?.data ?? null;
}

export async function saveIdMapEntry(clientRefKey, serverId, entityType = null) {
    await offlineDb.id_map.put({
        client_ref: clientRefKey,
        server_id: serverId,
        entity_type: entityType,
        updated_at: new Date().toISOString(),
    });
}

export async function getServerIdForClientRef(clientRefKey) {
    const row = await offlineDb.id_map.get(clientRefKey);

    return row?.server_id ?? null;
}
