const listeners = new Set();

let remoteReachable = typeof navigator !== 'undefined' ? navigator.onLine : true;
let lastNotifiedOnline = null;
let pollTimer = null;

function isElectronDesktop() {
    return window.autoSpaDesktop?.runtime === 'electron'
        || document.querySelector('meta[name="app-runtime"]')?.content === 'electron';
}

function canProbeElectronRemote() {
    return isElectronDesktop() && typeof window.autoSpaDesktop?.checkRemoteReachable === 'function';
}

export function isOnline() {
    if (canProbeElectronRemote()) {
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
    if (! canProbeElectronRemote()) {
        remoteReachable = navigator.onLine;

        return remoteReachable;
    }

    try {
        remoteReachable = Boolean(await window.autoSpaDesktop.checkRemoteReachable());
    } catch {
        remoteReachable = navigator.onLine;
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
    if (! canProbeElectronRemote()) {
        remoteReachable = true;
        emitConnectivity(true);

        return;
    }

    refreshAndEmit();
}

function handleBrowserOffline() {
    if (! canProbeElectronRemote()) {
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

    // Electron: poll desktop reachability so resume sync fires reliably.
    if (canProbeElectronRemote()) {
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
