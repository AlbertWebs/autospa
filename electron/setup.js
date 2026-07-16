import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { app } from 'electron';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const DESKTOP_ENV_MARKER = '.desktop-env-initialized';

export function resolveProjectRoot() {
    if (app.isPackaged) {
        return path.join(process.resourcesPath, 'app');
    }

    return path.resolve(__dirname, '..');
}

export function resolveUserLaravelRoot() {
    if (!app.isPackaged) {
        return resolveProjectRoot();
    }

    return path.join(app.getPath('userData'), 'laravel');
}

export function resolveEnvFilePath(laravelRoot) {
    if (app.isPackaged) {
        return path.join(laravelRoot, '.env');
    }

    const electronEnv = path.join(laravelRoot, '.env.electron');

    if (fs.existsSync(electronEnv)) {
        return electronEnv;
    }

    return path.join(app.getPath('userData'), '.env');
}

export function resolvePhpBinary(laravelRoot) {
    const platform = process.platform;
    const bundledCandidates = [
        path.join(laravelRoot, 'electron', 'php-runtime', platform, platform === 'win32' ? 'php.exe' : 'php'),
        path.join(__dirname, 'php-runtime', platform, platform === 'win32' ? 'php.exe' : 'php'),
    ];

    for (const candidate of bundledCandidates) {
        if (fs.existsSync(candidate)) {
            return candidate;
        }
    }

    return platform === 'win32' ? 'php.exe' : 'php';
}

export function parseEnvFile(filePath) {
    if (!fs.existsSync(filePath)) {
        return {};
    }

    const values = {};
    const lines = fs.readFileSync(filePath, 'utf8').split(/\r?\n/);

    for (const line of lines) {
        const trimmed = line.trim();

        if (trimmed === '' || trimmed.startsWith('#')) {
            continue;
        }

        const separator = trimmed.indexOf('=');

        if (separator === -1) {
            continue;
        }

        const key = trimmed.slice(0, separator).trim();
        let value = trimmed.slice(separator + 1).trim();

        if (
            (value.startsWith('"') && value.endsWith('"'))
            || (value.startsWith("'") && value.endsWith("'"))
        ) {
            value = value.slice(1, -1);
        }

        values[key] = value;
    }

    return values;
}

export function loadDesktopEnvironment(laravelRoot) {
    const envPath = resolveEnvFilePath(laravelRoot);
    const parsed = parseEnvFile(envPath);
    const values = { ...process.env };

    for (const [key, value] of Object.entries(parsed)) {
        if (value !== '') {
            values[key] = value;
        }
    }

    values.APP_RUNTIME = parsed.APP_RUNTIME ?? 'electron';

    return {
        envPath,
        values,
        parsed,
    };
}

export function hasAppKey(envValues) {
    const key = envValues.APP_KEY ?? '';

    return typeof key === 'string' && key.startsWith('base64:') && key.length > 16;
}

export function setEnvValue(envPath, key, value) {
    let contents = fs.existsSync(envPath) ? fs.readFileSync(envPath, 'utf8') : '';

    if (new RegExp(`^${key}=`, 'm').test(contents)) {
        contents = contents.replace(new RegExp(`^${key}=.*$`, 'm'), `${key}=${value}`);
    } else {
        contents += `${contents.endsWith('\n') || contents === '' ? '' : '\n'}${key}=${value}\n`;
    }

    fs.writeFileSync(envPath, contents);
}

function runArtisan(laravelRoot, envValues, args) {
    const php = resolvePhpBinary(laravelRoot);

    return new Promise((resolve, reject) => {
        const child = spawn(php, ['artisan', ...args], {
            cwd: laravelRoot,
            env: envValues,
            stdio: ['ignore', 'pipe', 'pipe'],
            windowsHide: true,
        });

        let stderr = '';
        let stdout = '';

        child.stdout.on('data', (chunk) => {
            stdout += chunk.toString();
        });

        child.stderr.on('data', (chunk) => {
            stderr += chunk.toString();
        });

        child.on('error', reject);
        child.on('close', (code) => {
            if (code === 0) {
                resolve(stdout.trim());
                return;
            }

            reject(new Error(stderr.trim() || stdout.trim() || `artisan ${args.join(' ')} failed with code ${code}`));
        });
    });
}

export async function ensureAppKey(laravelRoot, envPath, envValues) {
    if (hasAppKey(envValues)) {
        return envValues;
    }

    const generatedKey = await runArtisan(laravelRoot, envValues, ['key:generate', '--show', '--force']);

    if (!generatedKey.startsWith('base64:')) {
        throw new Error('Could not generate an application encryption key for the desktop app.');
    }

    setEnvValue(envPath, 'APP_KEY', generatedKey);

    return loadDesktopEnvironment(laravelRoot).values;
}

function copyDirectory(source, destination, filter = () => true) {
    fs.mkdirSync(destination, { recursive: true });

    for (const entry of fs.readdirSync(source, { withFileTypes: true })) {
        const sourcePath = path.join(source, entry.name);
        const destinationPath = path.join(destination, entry.name);

        if (!filter(sourcePath, entry)) {
            continue;
        }

        if (entry.isDirectory()) {
            copyDirectory(sourcePath, destinationPath, filter);
            continue;
        }

        fs.copyFileSync(sourcePath, destinationPath);
    }
}

function packagedCopyFilter(sourcePath, entry) {
    const normalized = sourcePath.replace(/\\/g, '/');
    const blocked = [
        '/electron/dist/',
        '/electron/node_modules/',
        '/node_modules/',
        '/.git/',
        '/tests/',
        '/storage/logs/',
        '/storage/framework/cache/',
        '/storage/framework/sessions/',
        '/storage/framework/views/',
    ];

    if (entry.isFile() && entry.name === '.env') {
        return false;
    }

    return !blocked.some((segment) => normalized.includes(segment));
}

export function ensurePackagedLaravelCopy(sourceRoot, destinationRoot) {
    const marker = path.join(destinationRoot, DESKTOP_ENV_MARKER);

    if (fs.existsSync(marker)) {
        return;
    }

    if (fs.existsSync(destinationRoot)) {
        fs.rmSync(destinationRoot, { recursive: true, force: true });
    }

    copyDirectory(sourceRoot, destinationRoot, packagedCopyFilter);
}

export function ensureDesktopEnvFile(laravelRoot, port) {
    const envPath = app.isPackaged
        ? path.join(laravelRoot, '.env')
        : resolveEnvFilePath(laravelRoot);

    if (!fs.existsSync(envPath)) {
        const templatePath = path.join(resolveProjectRoot(), '.env.electron.example');

        if (!fs.existsSync(templatePath)) {
            throw new Error('Missing .env.electron.example in the project root.');
        }

        fs.mkdirSync(path.dirname(envPath), { recursive: true });
        fs.copyFileSync(templatePath, envPath);
    }

    updateAppUrlInEnv(envPath, port);
    ensureDesktopEnvDefaults(envPath);

    const sqlitePath = path.join(laravelRoot, 'database', 'autospa_desktop.sqlite');
    fs.mkdirSync(path.dirname(sqlitePath), { recursive: true });

    if (!fs.existsSync(sqlitePath)) {
        fs.closeSync(fs.openSync(sqlitePath, 'a'));
    }

    return envPath;
}

export function updateAppUrlInEnv(envPath, port) {
    const appUrl = `http://127.0.0.1:${port}`;
    let contents = fs.existsSync(envPath) ? fs.readFileSync(envPath, 'utf8') : '';

    if (/^APP_URL=/m.test(contents)) {
        contents = contents.replace(/^APP_URL=.*$/m, `APP_URL=${appUrl}`);
    } else {
        contents += `${contents.endsWith('\n') || contents === '' ? '' : '\n'}APP_URL=${appUrl}\n`;
    }

    fs.writeFileSync(envPath, contents);
}

function ensureDesktopEnvDefaults(envPath) {
    const defaults = {
        APP_RUNTIME: 'electron',
        DB_CONNECTION: 'sqlite',
        DB_DATABASE: 'database/autospa_desktop.sqlite',
        DESKTOP_AUTO_SYNC: 'true',
        DESKTOP_REMOTE_URL: 'https://expresscarwash.co.ke',
        DESKTOP_CLIENT_HEADER: 'X-AutoSpa-Client',
        DESKTOP_CLIENT_VALUE: 'electron',
    };

    let contents = fs.existsSync(envPath) ? fs.readFileSync(envPath, 'utf8') : '';

    for (const [key, value] of Object.entries(defaults)) {
        if (!new RegExp(`^${key}=`, 'm').test(contents)) {
            contents += `${contents.endsWith('\n') || contents === '' ? '' : '\n'}${key}=${value}\n`;
        }
    }

    fs.writeFileSync(envPath, contents);
}

export async function ensureDesktopSetup(laravelRoot, port) {
    const sourceRoot = resolveProjectRoot();

    if (app.isPackaged) {
        ensurePackagedLaravelCopy(sourceRoot, laravelRoot);
    }

    const envPath = ensureDesktopEnvFile(laravelRoot, port);
    let { values: envValues } = loadDesktopEnvironment(laravelRoot);

    envValues = await ensureAppKey(laravelRoot, envPath, envValues);

    const marker = path.join(laravelRoot, DESKTOP_ENV_MARKER);

    if (!fs.existsSync(marker)) {
        await runArtisan(laravelRoot, envValues, ['migrate', '--force']);
        fs.writeFileSync(marker, new Date().toISOString());
    }

    return {
        envPath,
        envValues: loadDesktopEnvironment(laravelRoot).values,
    };
}

export function startPhpServer(laravelRoot, port, envValues) {
    const php = resolvePhpBinary(laravelRoot);

    return spawn(php, ['artisan', 'serve', '--host=127.0.0.1', `--port=${String(port)}`], {
        cwd: laravelRoot,
        env: {
            ...envValues,
            APP_URL: `http://127.0.0.1:${port}`,
        },
        stdio: ['ignore', 'pipe', 'pipe'],
        windowsHide: true,
    });
}

export async function findFreePort() {
    const net = await import('node:net');

    return new Promise((resolve, reject) => {
        const server = net.createServer();

        server.unref();
        server.on('error', reject);
        server.listen(0, '127.0.0.1', () => {
            const address = server.address();

            if (!address || typeof address === 'string') {
                reject(new Error('Could not resolve a free port.'));
                return;
            }

            const { port } = address;
            server.close(() => resolve(port));
        });
    });
}

export async function waitForServer(port, attempts = 90) {
    for (let attempt = 0; attempt < attempts; attempt += 1) {
        try {
            const response = await fetch(`http://127.0.0.1:${port}/up`);

            if (response.ok) {
                return;
            }
        } catch {
            // Server is still starting.
        }

        await new Promise((resolve) => setTimeout(resolve, 500));
    }

    throw new Error(`AutoSpa desktop server did not start on port ${port}.`);
}
