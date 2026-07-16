import { app, BrowserWindow, ipcMain, session, shell } from 'electron';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { REMOTE_LOGIN_PATH, REMOTE_SYNC_URL, START_PATH } from './config.js';
import {
    ensureDesktopSetup,
    findFreePort,
    loadDesktopEnvironment,
    resolveUserLaravelRoot,
    startPhpServer,
    waitForServer,
} from './setup.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const DESKTOP_CLIENT_HEADER = 'X-AutoSpa-Client';
const DESKTOP_CLIENT_VALUE = 'electron';

/** @type {import('node:child_process').ChildProcess | null} */
let phpProcess = null;

/** @type {import('electron').BrowserWindow | null} */
let mainWindow = null;

let serverPort = null;
let appUrl = null;
let remoteHeadersRegistered = false;

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
            registerIpcHandlers();
            registerRemoteSyncHeaders();
            await bootDesktopApp();
        } catch (error) {
            console.error(error);
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
        try {
            await bootDesktopApp();
        } catch (error) {
            console.error(error);
            app.quit();
        }
    }
});

app.on('before-quit', () => {
    stopPhpServer();
});

function registerIpcHandlers() {
    ipcMain.on('desktop:get-remote-sync-url', (event) => {
        event.returnValue = REMOTE_SYNC_URL ?? '';
    });

    ipcMain.handle('desktop:check-remote-reachable', async () => {
        return checkRemoteSession();
    });
}

function registerRemoteSyncHeaders() {
    if (!REMOTE_SYNC_URL || remoteHeadersRegistered) {
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
    const laravelRoot = resolveUserLaravelRoot();
    serverPort = await findFreePort();

    const setup = await ensureDesktopSetup(laravelRoot, serverPort);
    const environment = loadDesktopEnvironment(laravelRoot);

    phpProcess = startPhpServer(laravelRoot, serverPort, {
        ...environment.values,
        ...setup.envValues,
    });

    phpProcess.stdout?.on('data', (chunk) => {
        console.log(`[php] ${chunk.toString().trim()}`);
    });

    phpProcess.stderr?.on('data', (chunk) => {
        console.error(`[php] ${chunk.toString().trim()}`);
    });

    phpProcess.on('exit', (code) => {
        if (code !== null && code !== 0) {
            console.error(`PHP server exited with code ${code}`);
        }

        phpProcess = null;
    });

    await waitForServer(serverPort);

    appUrl = `http://127.0.0.1:${serverPort}${START_PATH}`;

    await createMainWindow(appUrl);
}

async function checkRemoteSession() {
    if (!REMOTE_SYNC_URL) {
        return false;
    }

    try {
        const response = await session.defaultSession.fetch(
            `${REMOTE_SYNC_URL}/desktop/sync/ping`,
            {
                headers: {
                    Accept: 'application/json',
                    [DESKTOP_CLIENT_HEADER]: DESKTOP_CLIENT_VALUE,
                },
            },
        );

        return response.status === 200;
    } catch {
        return false;
    }
}

async function ensureRemoteSession(window) {
    if (!REMOTE_SYNC_URL) {
        return;
    }

    if (await checkRemoteSession()) {
        return;
    }

    const loginUrl = `${REMOTE_SYNC_URL}${REMOTE_LOGIN_PATH}`;

    await new Promise((resolve) => {
        let settled = false;

        const finish = () => {
            if (settled) {
                return;
            }

            settled = true;
            window.webContents.removeListener('did-navigate', onNavigate);
            window.webContents.removeListener('did-finish-load', onFinishLoad);
            resolve();
        };

        const onNavigate = async (_event, url) => {
            if (!url.startsWith(REMOTE_SYNC_URL)) {
                return;
            }

            if (await checkRemoteSession()) {
                finish();
            }
        };

        const onFinishLoad = async () => {
            if (await checkRemoteSession()) {
                finish();
            }
        };

        window.webContents.on('did-navigate', onNavigate);
        window.webContents.on('did-finish-load', onFinishLoad);

        window.loadURL(loginUrl).catch(() => finish());
    });
}

async function createMainWindow(url) {
    mainWindow = new BrowserWindow({
        width: 1440,
        height: 900,
        minWidth: 1024,
        minHeight: 700,
        show: false,
        autoHideMenuBar: true,
        title: 'AutoSpa Pro',
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            contextIsolation: true,
            nodeIntegration: false,
            sandbox: true,
        },
    });

    mainWindow.on('ready-to-show', () => {
        mainWindow?.show();
    });

    const isLocalOrigin = (targetUrl) => targetUrl.startsWith('http://127.0.0.1')
        || targetUrl.startsWith('http://localhost')
        || targetUrl.startsWith('data:');

    const isRemoteOrigin = (targetUrl) => REMOTE_SYNC_URL && targetUrl.startsWith(REMOTE_SYNC_URL);

    const isAppOrigin = (targetUrl) => isLocalOrigin(targetUrl) || isRemoteOrigin(targetUrl);

    mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedUrl, isMainFrame) => {
        if (!isMainFrame || errorCode === -3 /* aborted */) {
            return;
        }

        if (!isLocalOrigin(validatedUrl)) {
            return;
        }

        const retryHtml = `
            <!DOCTYPE html>
            <html>
            <head><meta charset="utf-8"><title>AutoSpa Pro</title></head>
            <body style="margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0b1326;color:#dae2fd;font-family:system-ui,sans-serif;text-align:center;">
                <div>
                    <h2 style="margin-bottom:0.5rem;">Cannot start AutoSpa Pro</h2>
                    <p style="color:#8b90a0;margin-bottom:1.5rem;">The local app server is unavailable.<br><small>${errorDescription}</small></p>
                    <button onclick="location.href='${url}'" style="background:#adc6ff;color:#002e69;border:none;border-radius:0.5rem;padding:0.6rem 1.5rem;font-size:1rem;font-weight:600;cursor:pointer;">Retry</button>
                </div>
            </body>
            </html>`;

        mainWindow?.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(retryHtml)}`);
    });

    mainWindow.webContents.setWindowOpenHandler(({ url: targetUrl }) => {
        if (isAppOrigin(targetUrl)) {
            return { action: 'allow' };
        }

        shell.openExternal(targetUrl);

        return { action: 'deny' };
    });

    mainWindow.webContents.on('will-navigate', (event, targetUrl) => {
        if (isAppOrigin(targetUrl)) {
            return;
        }

        event.preventDefault();
        shell.openExternal(targetUrl);
    });

    if (REMOTE_SYNC_URL && await checkRemoteSession() === false) {
        const online = await isNetworkAvailable();

        if (online) {
            await ensureRemoteSession(mainWindow);
        }
    }

    await mainWindow.loadURL(url);

    if (!app.isPackaged) {
        mainWindow.webContents.openDevTools({ mode: 'detach' });
    }
}

async function isNetworkAvailable() {
    if (!REMOTE_SYNC_URL) {
        return false;
    }

    try {
        const response = await session.defaultSession.fetch(`${REMOTE_SYNC_URL}/up`, {
            method: 'GET',
        });

        return response.status >= 200 && response.status < 500;
    } catch {
        return false;
    }
}

function stopPhpServer() {
    if (!phpProcess || phpProcess.killed) {
        return;
    }

    phpProcess.kill('SIGTERM');

    if (process.platform === 'win32') {
        spawnTaskKill(phpProcess.pid);
    }
}

function spawnTaskKill(pid) {
    import('node:child_process').then(({ spawn }) => {
        spawn('taskkill', ['/pid', String(pid), '/f', '/t'], { windowsHide: true });
    }).catch(() => {
        // Best-effort cleanup on Windows.
    });
}
