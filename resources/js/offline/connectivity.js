const listeners = new Set();

let remoteReachable = typeof navigator !== 'undefined' ? navigator.onLine : true;
let pollTimer = null;

function isElectronDesktop() {
    return window.autoSpaDesktop?.runtime === 'electron'
        || document.querySelector('meta[name="app-runtime"]')?.content === 'electron';
}

function hasRemoteSync() {
    const meta = document.querySelector('meta[name="desktop-remote-sync-url"]')?.content?.trim();
    const bridge = window.autoSpaDesktop?.remoteSyncUrl?.trim?.() ?? '';

    return Boolean(meta || bridge);
}

export function isOnline() {
    if (isElectronDesktop() && hasRemoteSync()) {
        return remoteReachable;
    }

    return navigator.onLine;
}

function setTurboDrive(enabled) {
    const turbo = window.Turbo?.session;

    if (turbo) {
        turbo.drive = enabled;
    }
}

export function onConnectivityChange(callback) {
    listeners.add(callback);

    return () => listeners.delete(callback);
}

async function probeRemote() {
    if (!isElectronDesktop() || !hasRemoteSync()) {
        remoteReachable = navigator.onLine;

        return remoteReachable;
    }

    try {
        remoteReachable = await window.autoSpaDesktop?.checkRemoteReachable?.() ?? false;
    } catch {
        remoteReachable = false;
    }

    return remoteReachable;
}

async function notify() {
    const wasOnline = isOnline();
    await probeRemote();
    const online = isOnline();

    setTurboDrive(online);

    if (online !== wasOnline) {
        listeners.forEach((callback) => callback(online));
    }
}

function pollRemoteReachability() {
    probeRemote().then(() => {
        const online = isOnline();
        setTurboDrive(online);
        listeners.forEach((callback) => callback(online));
    });
}

export function initConnectivity() {
    probeRemote().then(() => {
        setTurboDrive(isOnline());
    });

    window.addEventListener('online', notify);
    window.addEventListener('offline', notify);

    if (isElectronDesktop() && hasRemoteSync()) {
        pollTimer = window.setInterval(pollRemoteReachability, 30000);
    }
}

export function destroyConnectivity() {
    window.removeEventListener('online', notify);
    window.removeEventListener('offline', notify);

    if (pollTimer !== null) {
        window.clearInterval(pollTimer);
        pollTimer = null;
    }

    listeners.clear();
}
