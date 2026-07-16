# AutoSpa Pro — Desktop (Electron)

## Modes

- **Online:** loads the live web app at `https://expresscarwash.co.ke` — identical UI/UX to the browser.
- **Offline:** local operable-only shell (POS, Live Wash Board, New Job Card, Check In Vehicle) styled with the same web CSS. Changes queue in SQLite and sync via `/desktop/sync/*` when back online.

## Development

```bash
# From project root — build web CSS first (once / after UI changes)
npm run build

cd electron
npm install
npm run electron:dev
```

## Windows EXE

```bash
cd electron
npm run electron:build:win
```

Installer: `dist/AutoSpa Pro-Setup-1.4.1.exe`

Offline tools: POS, Live Wash Board, New Job Card, Check In Vehicle, Finance (overview / income / expenses / P&amp;L).
