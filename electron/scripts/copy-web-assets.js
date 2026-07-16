import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '../..');
const manifestPath = path.join(projectRoot, 'public', 'build', 'manifest.json');
const outDir = path.join(__dirname, '../renderer/assets');
const indexPath = path.join(__dirname, '../renderer/index.html');
const logoSrc = path.join(projectRoot, 'public', 'logo.png');
const logoDest = path.join(__dirname, '../renderer/logo.png');

fs.mkdirSync(outDir, { recursive: true });

if (!fs.existsSync(manifestPath)) {
    console.error('Missing public/build/manifest.json. Run npm run build in the project root first.');
    process.exit(1);
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const cssEntries = [];

for (const entry of Object.values(manifest)) {
    if (entry.file && entry.file.endsWith('.css')) {
        cssEntries.push(entry.file);
    }

    if (Array.isArray(entry.css)) {
        for (const css of entry.css) {
            cssEntries.push(css);
        }
    }
}

const uniqueCss = [...new Set(cssEntries)].filter((file) => !file.includes('auth'));
const copied = [];

for (const file of fs.readdirSync(outDir)) {
    if (file.endsWith('.css')) {
        fs.unlinkSync(path.join(outDir, file));
    }
}

for (const relative of uniqueCss) {
    const src = path.join(projectRoot, 'public', 'build', relative);
    const destName = path.basename(relative);
    const dest = path.join(outDir, destName);

    if (!fs.existsSync(src)) {
        console.warn(`Skip missing asset: ${src}`);
        continue;
    }

    fs.copyFileSync(src, dest);
    copied.push(destName);
}

fs.writeFileSync(
    path.join(outDir, 'assets.json'),
    JSON.stringify({ css: copied }, null, 2),
);

if (fs.existsSync(logoSrc)) {
    fs.copyFileSync(logoSrc, logoDest);
}

if (fs.existsSync(indexPath) && copied.length > 0) {
    let html = fs.readFileSync(indexPath, 'utf8');
    const nl = html.includes('\r\n') ? '\r\n' : '\n';
    const assetLinks = copied
        .map((name) => `    <link rel="stylesheet" href="./assets/${name}" />`)
        .join(nl);

    html = html.replace(
        /(?:    <link rel="stylesheet" href="\.\/assets\/[^"]+\.css" \/>\r?\n)+/,
        `${assetLinks}${nl}`,
    );

    fs.writeFileSync(indexPath, html);
}

console.log(`Copied ${copied.length} CSS asset(s) to electron/renderer/assets`);
