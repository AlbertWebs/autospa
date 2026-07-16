import { app, BrowserWindow, dialog, session, shell } from 'electron';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import {
    DESKTOP_CLIENT_HEADER,
    DESKTOP_CLIENT_VALUE,
    REMOTE_LOGIN_PATH,
    REMOTE_SYNC_URL,
} from './config.js';
import { closeDatabase, initDatabase, pendingCount } from './src/db.js';
import { registerIpcHandlers } from './src/ipc.js';
import { checkRemoteSession, isRemoteReachable, syncNow } from './src/sync.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const APP_ICON = path.join(__dirname, 'renderer', 'logo.png');

const REMOTE_HOME = `${REMOTE_SYNC_URL.replace(/\/$/, '')}/dashboard`;
const REMOTE_LOGIN = `${REMOTE_SYNC_URL.replace(/\/$/, '')}${REMOTE_LOGIN_PATH}`;

/** @type {BrowserWindow | null} */
let mainWindow = null;
let remoteHeadersRegistered = false;
/** @type {'remote' | 'offline' | 'boot'} */
let uiMode = 'boot';
let modeWatchTimer = null;
let switchingMode = false;

const gotLock = app.requestSingleInstanceLock();

if (!gotLock) {
    app.quit();
} else {
    app.on('second-instance', () => {
        if (mainWindow) {
            if (mainWindow.isMinimized()) {
                mainWindow.restore();
            }

            mainWindow.focus();
        }
    });

    app.whenReady().then(async () => {
        try {
            // Helps Windows use this app's icon in the taskbar instead of a generic Electron icon.
            if (process.platform === 'win32') {
                app.setAppUserModelId('com.autospa.desktop');
            }

            initDatabase();
            registerRemoteSyncHeaders();
            registerIpcHandlers({
                openCloudLogin: () => ensureRemoteSession({ returnToRemote: true }),
                goOnline: async () => {
                    if (!(await isRemoteReachable())) {
                        dialog.showMessageBox(mainWindow, {
                            type: 'warning',
                            title: 'Still offline',
                            message: 'Cloud is not reachable yet. Keep working in offline mode.',
                        });

                        return;
                    }

                    const syncResult = await syncNow().catch((error) => {
                        console.error('Resume sync failed', error);

                        return { synced: 0, failed: 0, reason: 'error', pending: pendingCount() };
                    });

                    const remaining = pendingCount();
                    const sessionOk = await checkRemoteSession();

                    if (!sessionOk.ok) {
                        if (remaining > 0) {
                            dialog.showMessageBox(mainWindow, {
                                type: 'warning',
                                title: 'Sign in required',
                                message: `${remaining} offline change(s) are queued. Sign in to upload them.`,
                            });
                        }

                        await ensureRemoteSession({ returnToRemote: true });
                        return;
                    }

                    if (remaining > 0) {
                        dialog.showMessageBox(mainWindow, {
                            type: 'warning',
                            title: 'Sync incomplete',
                            message: `${remaining} change(s) could not be uploaded yet (${syncResult.reason || 'retry from Mission Control'}). You can sync again from the dashboard.`,
                        });
                    }

                    await switchToRemote();
                },
                goOffline: () => switchToOffline(),
            });
            await bootDesktopApp();
        } catch (error) {
            console.error(error);
            dialog.showErrorBox(
                'AutoSpa Pro failed to start',
                error instanceof Error ? error.message : String(error),
            );
            app.quit();
        }
    });
}

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', async () => {
    if (BrowserWindow.getAllWindows().length === 0) {
        await bootDesktopApp();
    }
});

app.on('before-quit', () => {
    if (modeWatchTimer) {
        clearInterval(modeWatchTimer);
    }

    closeDatabase();
});

function registerRemoteSyncHeaders() {
    if (remoteHeadersRegistered) {
        return;
    }

    const remoteOrigin = new URL(REMOTE_SYNC_URL).origin;

    session.defaultSession.webRequest.onBeforeSendHeaders(
        { urls: [`${remoteOrigin}/*`] },
        (details, callback) => {
            details.requestHeaders[DESKTOP_CLIENT_HEADER] = DESKTOP_CLIENT_VALUE;
            callback({ requestHeaders: details.requestHeaders });
        },
    );

    remoteHeadersRegistered = true;
}

async function bootDesktopApp() {
    ensureMainWindow();
    await showBootScreen('Opening AutoSpa Pro...');

    const reachable = await isRemoteReachable();

    if (reachable) {
        const sessionOk = await checkRemoteSession();

        if (!sessionOk.ok) {
            await showBootScreen('Sign in to continue…');
            await ensureRemoteSession({ returnToRemote: true });
        } else {
            await switchToRemote();
        }
    } else {
        await switchToOffline();
    }

    syncNow().catch((error) => {
        console.error('Background sync failed', error);
    });

    startModeWatch();
    setInterval(() => {
        const pending = pendingCount();

        // Keep retrying while anything is queued; otherwise soft-poll less often.
        if (pending > 0 || uiMode === 'remote') {
            syncNow().catch(() => {});
        }
    }, 20000);
}

function startModeWatch() {
    if (modeWatchTimer) {
        clearInterval(modeWatchTimer);
    }

    modeWatchTimer = setInterval(async () => {
        if (switchingMode || !mainWindow || mainWindow.isDestroyed()) {
            return;
        }

        const reachable = await isRemoteReachable();

        if (uiMode === 'remote' && !reachable) {
            await switchToOffline();
            return;
        }

        if (uiMode === 'offline' && reachable) {
            const syncResult = await syncNow().catch((error) => {
                console.error('Resume sync failed', error);

                return { pending: pendingCount() };
            });

            const sessionOk = await checkRemoteSession();
            const remaining = pendingCount();

            if (sessionOk.ok) {
                // Return to live UI even if some items remain — Mission Control can finish sync.
                await switchToRemote();

                if (remaining > 0) {
                    console.warn('Switched online with pending outbox items', remaining, syncResult);
                }
            }
        }

        // While remote, keep draining SQLite outbox in the background.
        if (uiMode === 'remote' && reachable && pendingCount() > 0) {
            syncNow().catch(() => {});
        }
    }, 5000);
}

function ensureMainWindow() {
    if (mainWindow && !mainWindow.isDestroyed()) {
        return;
    }

    mainWindow = new BrowserWindow({
        width: 1440,
        height: 900,
        minWidth: 1024,
        minHeight: 700,
        show: false,
        autoHideMenuBar: true,
        title: 'AutoSpa Pro',
        icon: APP_ICON,
        backgroundColor: '#0b1326',
        webPreferences: {
            preload: path.join(__dirname, 'preload.cjs'),
            contextIsolation: true,
            nodeIntegration: false,
            sandbox: true,
        },
    });

    mainWindow.on('ready-to-show', () => {
        mainWindow?.show();
    });

    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        if (url.startsWith(REMOTE_SYNC_URL) || url.startsWith('file:') || url.startsWith('data:')) {
            return { action: 'allow' };
        }

        shell.openExternal(url);

        return { action: 'deny' };
    });

    mainWindow.webContents.on('will-navigate', (event, url) => {
        if (url.startsWith(REMOTE_SYNC_URL) || url.startsWith('file:') || url.startsWith('data:')) {
            return;
        }

        event.preventDefault();
        shell.openExternal(url);
    });

    mainWindow.webContents.on('did-fail-load', async (_event, errorCode, _errorDescription, validatedUrl, isMainFrame) => {
        if (!isMainFrame || errorCode === -3) {
            return;
        }

        if (uiMode === 'remote' && validatedUrl.startsWith(REMOTE_SYNC_URL)) {
            await switchToOffline();
        }
    });
}

async function showBootScreen(message) {
    if (!mainWindow || mainWindow.isDestroyed()) {
        return;
    }

    uiMode = 'boot';

    const html = `
        <!DOCTYPE html>
        <html class="dark">
        <head><meta charset="utf-8"><title>AutoSpa Pro</title></head>
        <body style="margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0b1326;color:#dae2fd;font-family:system-ui,sans-serif;text-align:center;">
            <div style="max-width:32rem;padding:1.5rem;">
                <h2 style="margin-bottom:0.5rem;">AutoSpa Pro</h2>
                <p style="color:#8b90a0;margin:0;">${message}</p>
            </div>
        </body>
        </html>`;

    await mainWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(html)}`);
}

async function switchToRemote() {
    if (!mainWindow || mainWindow.isDestroyed() || switchingMode) {
        return;
    }

    switchingMode = true;

    try {
        uiMode = 'remote';
        await mainWindow.loadURL(REMOTE_HOME);
        syncNow().catch(() => {});
    } finally {
        switchingMode = false;
    }
}

async function switchToOffline() {
    if (!mainWindow || mainWindow.isDestroyed() || switchingMode) {
        return;
    }

    switchingMode = true;

    try {
        uiMode = 'offline';
        const indexHtml = path.join(__dirname, 'renderer', 'index.html');
        await mainWindow.loadFile(indexHtml);
    } finally {
        switchingMode = false;
    }
}

async function ensureRemoteSession({ returnToRemote = true } = {}) {
    if ((await checkRemoteSession()).ok) {
        if (returnToRemote) {
            await switchToRemote();
        }

        return;
    }

    if (!mainWindow) {
        return;
    }

    await new Promise((resolve) => {
        let settled = false;
        const timeout = setTimeout(() => finish(), 180000);

        const finish = () => {
            if (settled) {
                return;
            }

            settled = true;
            clearTimeout(timeout);
            mainWindow?.webContents.removeListener('did-navigate', onNavigate);
            mainWindow?.webContents.removeListener('did-finish-load', onFinishLoad);
            resolve();
        };

        const onNavigate = async (_event, url) => {
            if (!url.startsWith(REMOTE_SYNC_URL)) {
                return;
            }

            if (url.includes('/login') || url.includes('/forgot-password') || url.includes('/reset-password')) {
                return;
            }

            if ((await checkRemoteSession()).ok) {
                finish();
            }
        };

        const onFinishLoad = async () => {
            const url = mainWindow?.webContents.getURL() ?? '';

            if (!url.startsWith(REMOTE_SYNC_URL) || url.includes('/login')) {
                return;
            }

            if ((await checkRemoteSession()).ok) {
                finish();
            }
        };

        mainWindow.webContents.on('did-navigate', onNavigate);
        mainWindow.webContents.on('did-finish-load', onFinishLoad);
        mainWindow.loadURL(REMOTE_LOGIN).catch(() => finish());
    });

    if (returnToRemote && (await checkRemoteSession()).ok) {
        await switchToRemote();
    } else if (!(await isRemoteReachable())) {
        await switchToOffline();
    }
}

