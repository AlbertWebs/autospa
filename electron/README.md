# AutoSpa Pro — Desktop (Electron)

Offline-first desktop distribution for AutoSpa. The web deployment is unchanged; all desktop-specific code lives in this `electron/` folder.

## How it works

1. Electron starts a local PHP server (`php artisan serve`) bound to `127.0.0.1`.
2. Laravel runs with `APP_RUNTIME=electron` and a local SQLite database.
3. The existing offline stack (Dexie outbox, sync badge, `SyncController`) is reused.
4. Service workers are disabled in desktop mode to avoid conflicting with Electron's network stack.

## Requirements

- **PHP 8.2+** on PATH (`php -v`)
- **Composer dependencies** installed in the project root (`composer install`)
- **Built frontend assets** (`npm run build` in project root)
- **Node.js 20+** for Electron tooling

Optional: place a portable PHP runtime at `electron/php-runtime/<platform>/php(.exe)` to bundle PHP with installers.

## Development workflow

### 1. Prepare the desktop environment file

From the project root:

```bash
cp .env.electron.example .env.electron
```

Edit `.env.electron` if needed. This file is **not** used by the web app.

### 2. Build frontend assets (once per UI change)

```bash
npm run build
```

### 3. Run Electron

```bash
cd electron
npm install
npm run electron:dev
```

Electron will:

- Read `.env.electron` and pass those values to the PHP process (overriding the web `.env` for desktop-only keys).
- Start `php artisan serve` on a free local port.
- Wait for `/up`, then open the app window.

### Alternative: manual Laravel + Electron

```bash
# Terminal 1 — copy env and serve with electron variables loaded manually
cp .env.electron.example .env.electron
# Windows PowerShell example:
Get-Content .env.electron | ForEach-Object { if ($_ -match '^([^#=]+)=(.*)$') { $env:$($matches[1]) = $matches[2] } }
php artisan serve

# Terminal 2
cd electron && npm run electron:dev
```

## Production build

From the project root:

```bash
npm ci
npm run build
composer install --no-dev --optimize-autoloader
cd electron
npm ci
npm run electron:build:win    # Windows installer
npm run electron:build:mac    # macOS dmg
npm run electron:build:linux  # Linux AppImage
```

Installers are written to `electron/dist/`.

On first packaged launch, the app copies the Laravel project into the user data directory, creates `database/autospa_desktop.sqlite`, runs migrations, and opens the setup wizard.

## Go live checklist

### Internal staff rollout

1. Build the installer for the target OS.
2. Distribute via USB, shared drive, or MDM.
3. User installs and launches — local SQLite DB is created automatically.
4. Complete the first-time setup wizard.
5. Optional: set `DESKTOP_REMOTE_URL` in the desktop `.env` (user data dir when packaged) for cloud sync.
6. Staff work offline locally; the sync badge shows pending cloud uploads when configured.

### Customer distribution

1. Code-sign Windows (Authenticode) and macOS (Apple Developer ID) builds.
2. Host installers on your website or GitHub Releases.
3. Document minimum requirements: Windows 10+, 4 GB RAM, ~200 MB disk (more if bundling PHP).

## Configuration

| Variable | Purpose |
|----------|---------|
| `APP_RUNTIME=electron` | Enables desktop-only Laravel/JS behavior |
| `DB_CONNECTION=sqlite` | Local offline database |
| `DESKTOP_REMOTE_URL` | Optional cloud server for `/sync/*` when online |
| `DESKTOP_AUTO_SYNC` | Reserved for automatic remote sync (default: true) |

## Web app safety

- Root `package.json` and `composer.json` scripts are unchanged.
- Production `.env` and deploy pipeline are untouched.
- Desktop behavior is gated by `APP_RUNTIME=electron` only.

## Troubleshooting

| Issue | Fix |
|-------|-----|
| Blank window | Ensure `npm run build` was run and `public/build` exists |
| PHP not found | Install PHP 8.2+ or add portable runtime under `electron/php-runtime/` |
| Port in use | Restart the app; it picks a free port automatically |
| Reset desktop DB | Delete `%APPDATA%/autospa-desktop/laravel` (Windows) or equivalent userData path |
| `Electron failed to install correctly` | A previous `npm install` was interrupted. Kill stuck `node install.js` processes, delete `electron/node_modules`, then reinstall. See below. |

### Electron binary failed to download

If you see:

```
Error: Electron failed to install correctly, please delete node_modules/electron and try installing again
```

1. **Kill stuck installs** — an interrupted download leaves an empty `node_modules/electron` folder locked:
   ```powershell
   Get-Process node | Stop-Process -Force
   Remove-Item -Recurse -Force electron\node_modules
   ```

2. **Reinstall** (uses mirror in `electron/.npmrc` for faster downloads):
   ```powershell
   cd electron
   npm install
   ```
   The Electron binary is ~115 MB; allow several minutes on slow connections.

3. **Manual download** (if npm keeps failing):
   ```powershell
   $dist = "electron\node_modules\electron\dist"
   $zip = "$env:TEMP\electron-v35.7.5-win32-x64.zip"
   New-Item -ItemType Directory -Force -Path $dist
   curl.exe -L -o $zip "https://github.com/electron/electron/releases/download/v35.7.5/electron-v35.7.5-win32-x64.zip"
   Expand-Archive -Path $zip -DestinationPath $dist -Force
   Set-Content -Path "electron\node_modules\electron\path.txt" -Value "electron.exe" -NoNewline
   Set-Content -Path "$dist\version" -Value "35.7.5" -NoNewline
   ```
