const listeners = new Set();

export function isOnline() {
    return navigator.onLine;
}

export function onConnectivityChange(callback) {
    listeners.add(callback);

    return () => listeners.delete(callback);
}

function notify() {
    listeners.forEach((callback) => callback(isOnline()));
}

export function initConnectivity() {
    window.addEventListener('online', notify);
    window.addEventListener('offline', notify);
}

export function destroyConnectivity() {
    window.removeEventListener('online', notify);
    window.removeEventListener('offline', notify);
    listeners.clear();
}
