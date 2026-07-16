import { app, BrowserWindow, shell } from 'electron';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { REMOTE_URL, START_PATH } from './config.js';
import {
    ensureDesktopSetup,
    findFreePort,
    loadDesktopEnvironment,
    resolveUserLaravelRoot,
    startPhpServer,
    waitForServer,
} from './setup.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** @type {import('node:child_process').ChildProcess | null} */
let phpProcess = null;

/** @type {import('electron').BrowserWindow | null} */
let mainWindow = null;

let serverPort = null;
let appUrl = null;

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

async function bootDesktopApp() {
    if (REMOTE_URL) {
        // Remote mode always needs the network, so stale service workers and
        // cached pages only cause trouble (e.g. 419 from cached CSRF tokens).
        // Cookies are kept so "remember me" sessions survive restarts.
        const { session } = await import('electron');
        await session.defaultSession.clearStorageData({
            storages: ['serviceworkers', 'cachestorage'],
        });
        await session.defaultSession.clearCache();

        // The site root is the public landing page, which has no place in the
        // desktop app. Rewrite it to the login screen (after logout, brand
        // link clicks, etc.). Authenticated sessions are bounced onward to
        // the dashboard by the server.
        session.defaultSession.webRequest.onBeforeRequest(
            { urls: [`${REMOTE_URL}/`] },
            (details, callback) => {
                if (details.resourceType === 'mainFrame') {
                    callback({ redirectURL: `${REMOTE_URL}${START_PATH}` });

                    return;
                }

                callback({});
            },
        );

        appUrl = `${REMOTE_URL}${START_PATH}`;
        await createMainWindow(appUrl);

        return;
    }

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

    appUrl = `http://127.0.0.1:${serverPort}`;
    await createMainWindow(appUrl);
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

    // 419 Page Expired (stale CSRF token): recover by loading a fresh login
    // page instead of showing the error to the user.
    mainWindow.webContents.on('did-navigate', (event, navigatedUrl, httpResponseCode) => {
        if (httpResponseCode === 419 && REMOTE_URL) {
            mainWindow?.loadURL(`${REMOTE_URL}${START_PATH}`);
        }
    });

    // If the hosted site is unreachable (e.g. no internet), show a retry screen
    // instead of a blank window.
    mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedUrl, isMainFrame) => {
        if (!isMainFrame || errorCode === -3 /* aborted */) {
            return;
        }

        const retryHtml = `
            <!DOCTYPE html>
            <html>
            <head><meta charset="utf-8"><title>AutoSpa Pro</title></head>
            <body style="margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0b1326;color:#dae2fd;font-family:system-ui,sans-serif;text-align:center;">
                <div>
                    <h2 style="margin-bottom:0.5rem;">Cannot reach AutoSpa Pro</h2>
                    <p style="color:#8b90a0;margin-bottom:1.5rem;">Check your internet connection and try again.<br><small>${errorDescription}</small></p>
                    <button onclick="location.href='${url}'" style="background:#adc6ff;color:#002e69;border:none;border-radius:0.5rem;padding:0.6rem 1.5rem;font-size:1rem;font-weight:600;cursor:pointer;">Retry</button>
                </div>
            </body>
            </html>`;

        mainWindow?.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(retryHtml)}`);
    });

    const isAppOrigin = (targetUrl) => targetUrl.startsWith('http://127.0.0.1')
        || targetUrl.startsWith('http://localhost')
        || targetUrl.startsWith('data:')
        || (REMOTE_URL && isSameHost(targetUrl, REMOTE_URL));

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

    await mainWindow.loadURL(url);

    if (!app.isPackaged) {
        mainWindow.webContents.openDevTools({ mode: 'detach' });
    }
}

function isSameHost(url, base) {
    try {
        const strip = (host) => host.replace(/^www\./, '');

        return strip(new URL(url).host) === strip(new URL(base).host);
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
