const listeners = new Set();

let remoteReachable = typeof navigator !== 'undefined' ? navigator.onLine : true;
let lastNotifiedOnline = null;
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

function emitConnectivity(online) {
    setTurboDrive(online);

    if (lastNotifiedOnline === online) {
        return;
    }

    lastNotifiedOnline = online;
    listeners.forEach((callback) => callback(online));
}

async function refreshAndEmit() {
    await probeRemote();
    emitConnectivity(isOnline());
}

function handleBrowserOnline() {
    if (!(isElectronDesktop() && hasRemoteSync())) {
        remoteReachable = true;
        emitConnectivity(true);

        return;
    }

    refreshAndEmit();
}

function handleBrowserOffline() {
    if (!(isElectronDesktop() && hasRemoteSync())) {
        remoteReachable = false;
        emitConnectivity(false);

        return;
    }

    refreshAndEmit();
}

export function initConnectivity() {
    probeRemote().then(() => {
        lastNotifiedOnline = isOnline();
        setTurboDrive(lastNotifiedOnline);
    });

    window.addEventListener('online', handleBrowserOnline);
    window.addEventListener('offline', handleBrowserOffline);

    // Faster resume detection for Electron remote reachability.
    if (isElectronDesktop() && hasRemoteSync()) {
        pollTimer = window.setInterval(() => {
            refreshAndEmit();
        }, 5000);
    }
}

export function destroyConnectivity() {
    window.removeEventListener('online', handleBrowserOnline);
    window.removeEventListener('offline', handleBrowserOffline);

    if (pollTimer !== null) {
        window.clearInterval(pollTimer);
        pollTimer = null;
    }

    listeners.clear();
}
