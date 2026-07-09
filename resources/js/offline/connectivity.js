const listeners = new Set();

export function isOnline() {
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

function notify() {
    const online = isOnline();

    setTurboDrive(online);
    listeners.forEach((callback) => callback(online));
}

export function initConnectivity() {
    setTurboDrive(isOnline());
    window.addEventListener('online', notify);
    window.addEventListener('offline', notify);
}

export function destroyConnectivity() {
    window.removeEventListener('online', notify);
    window.removeEventListener('offline', notify);
    listeners.clear();
}
