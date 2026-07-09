import { app, BrowserWindow, shell } from 'electron';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
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

    mainWindow.webContents.setWindowOpenHandler(({ url: targetUrl }) => {
        if (targetUrl.startsWith('http://127.0.0.1') || targetUrl.startsWith('http://localhost')) {
            return { action: 'allow' };
        }

        shell.openExternal(targetUrl);

        return { action: 'deny' };
    });

    await mainWindow.loadURL(url);

    if (!app.isPackaged) {
        mainWindow.webContents.openDevTools({ mode: 'detach' });
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
