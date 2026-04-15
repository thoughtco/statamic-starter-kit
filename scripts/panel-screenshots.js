/**
 * Panel Screenshots Generator
 * Generates screenshot thumbnails for each Statamic replicator panel set.
 *
 * Everything is auto-discovered — no explicit panel list required:
 *
 *   1. Scans resources/views/_panels/*.antlers.html for panel handles.
 *   2. For each handle, visits /__panel-screenshot/{handle} — a dedicated route
 *      that renders just that panel (with real CMS data) inside #screenshot-output.
 *   3. Screenshots the #screenshot-output element.
 *   4. Writes a Statamic .meta YAML file alongside each image.
 *   5. Updates the image: path in any blueprint YAML that references the handle.
 *   6. Runs `php please stache:clear` once all screenshots are done.
 *
 * Usage:
 *   node scripts/panel-screenshots.js [base-url] [handle]
 *
 *   base-url  optional  defaults to APP_URL from .env, then http://localhost:8000
 *   handle    optional  re-run a single panel instead of all
 *
 * Examples:
 *   node scripts/panel-screenshots.js
 *   node scripts/panel-screenshots.js https://agconnect.test
 *   node scripts/panel-screenshots.js https://agconnect.test page_hero
 *
 * Output saved to : public/assets/files/panel-screenshots/{handle}.jpg
 * YAML preview    : assets/files/panel-screenshots/{handle}.jpg
 */

import puppeteer from 'puppeteer';
import { execSync } from 'child_process';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname  = path.dirname(__filename);
const ROOT       = path.join(__dirname, '..');

const BASE_URL   = (process.argv[2] || readEnvUrl() || 'http://localhost:8000').replace(/\/$/, '');
const ONLY       = process.argv[3] ?? null;

const PANELS_DIR    = path.join(ROOT, 'resources', 'views', '_panels');
const BLUEPRINT_DIR = path.join(ROOT, 'resources', 'blueprints');
const OUTPUT_DIR    = path.join(ROOT, 'public', 'assets', 'files', 'set-previews');
const META_DIR      = path.join(OUTPUT_DIR, '.meta');

// ---------------------------------------------------------------------------
// .env reader
// ---------------------------------------------------------------------------

/** Read APP_URL from the project's .env file, stripping surrounding quotes. */
function readEnvUrl() {
    const envFile = path.join(ROOT, '.env');
    if (!fs.existsSync(envFile)) return null;
    const match = fs.readFileSync(envFile, 'utf8').match(/^APP_URL=["']?([^"'\r\n]+)["']?/m);
    return match ? match[1].trim() : null;
}

// ---------------------------------------------------------------------------
// Panel discovery
// ---------------------------------------------------------------------------

/** Return all panel handles found in resources/views/_panels/. */
function discoverHandles() {
    return fs.readdirSync(PANELS_DIR)
        .filter(f => f.endsWith('.antlers.html'))
        .map(f => f.replace(/\.antlers\.html$/, ''))
        .sort();
}

// ---------------------------------------------------------------------------
// Statamic .meta file writer
// ---------------------------------------------------------------------------

/**
 * Write a Statamic asset .meta YAML file for a saved image.
 * Path convention: {asset_dir}/.meta/{filename}.yaml
 */
function writeMetaFile(imagePath, width, height) {
    const stat     = fs.statSync(imagePath);
    const filename = path.basename(imagePath);

    fs.mkdirSync(META_DIR, { recursive: true });

    const meta = [
        `data: []`,
        `size: ${stat.size}`,
        `last_modified: ${Math.floor(stat.mtimeMs / 1000)}`,
        `width: ${Math.round(width)}`,
        `height: ${Math.round(height)}`,
        `mime_type: image/jpeg`,
        `duration: null`,
    ].join('\n') + '\n';

    fs.writeFileSync(path.join(META_DIR, `${filename}.yaml`), meta, 'utf8');
}

// ---------------------------------------------------------------------------
// Blueprint YAML updater
// ---------------------------------------------------------------------------

/**
 * Search all blueprint YAML files for a replicator set block matching `handle`
 * and add or replace its `image:` line.
 * Works line-by-line to preserve all existing formatting and comments.
 */
function updateBlueprintPreview(handle, previewPath) {
    const yamlFiles = findFiles(BLUEPRINT_DIR, f => f.endsWith('.yaml') || f.endsWith('.yml'));
    let updated = 0;

    for (const file of yamlFiles) {
        const original = fs.readFileSync(file, 'utf8');
        const patched  = patchYamlPreview(original, handle, previewPath);
        if (patched !== original) {
            fs.writeFileSync(file, patched, 'utf8');
            updated++;
        }
    }

    return updated;
}

function patchYamlPreview(src, handle, previewPath) {
    const lines = src.split('\n');
    const out   = [];
    let i = 0;

    while (i < lines.length) {
        const line     = lines[i];
        const keyMatch = line.match(/^(\s+)([\w]+):\s*$/);

        if (keyMatch && keyMatch[2] === handle) {
            const baseIndent = keyMatch[1];
            const propIndent = baseIndent + '  ';

            i++;
            const blockLines = [];

            while (i < lines.length) {
                const bl = lines[i];
                if (bl.trim() === '' || (bl.trim() !== '' && !bl.startsWith(baseIndent + ' '))) break;
                blockLines.push(bl);
                i++;
            }

            // Only patch blocks that look like replicator set entries (have a display: key)
            const hasDisplay = blockLines.some(l => l.trim().startsWith('display:'));
            if (!hasDisplay) {
                out.push(line, ...blockLines);
                continue;
            }

            const previewLine  = `${propIndent}image: ${previewPath}`;
            const existingIdx  = blockLines.findIndex(l => /^\s+image:/.test(l));
            const fieldsIdx    = blockLines.findIndex(l => l.trim().startsWith('fields:'));

            if (existingIdx >= 0) {
                blockLines[existingIdx] = previewLine;
            } else {
                const insertAt = fieldsIdx >= 0 ? fieldsIdx : blockLines.length;
                blockLines.splice(insertAt, 0, previewLine);
            }

            out.push(line, ...blockLines);
        } else {
            out.push(line);
            i++;
        }
    }

    return out.join('\n');
}

// ---------------------------------------------------------------------------
// File system utility
// ---------------------------------------------------------------------------

function findFiles(dir, predicate) {
    const results = [];
    if (!fs.existsSync(dir)) return results;
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);
        if (entry.isDirectory()) results.push(...findFiles(full, predicate));
        else if (predicate(entry.name)) results.push(full);
    }
    return results;
}

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

async function run() {
    const allHandles = discoverHandles();
    const handles    = ONLY ? allHandles.filter(h => h === ONLY) : allHandles;

    if (ONLY && handles.length === 0) {
        console.error(`Unknown panel handle: "${ONLY}"`);
        console.error(`Discovered handles:\n  ${allHandles.join('\n  ')}`);
        process.exit(1);
    }

    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    fs.mkdirSync(META_DIR, { recursive: true });

    console.log(`Panel Screenshots — ${handles.length} panel${handles.length !== 1 ? 's' : ''}`);
    console.log(`Base URL : ${BASE_URL}`);
    console.log(`Output   : ${OUTPUT_DIR}\n`);

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900 });

    let succeeded = 0;

    for (const handle of handles) {
        const url        = `${BASE_URL}/__panel-screenshot/${handle}`;
        const outputPath = path.join(OUTPUT_DIR, `${handle}.jpg`);

        process.stdout.write(`  [${handle}] `);

        try {
            const response = await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });

            if (response.status() === 404) {
                console.log(`SKIP — 404 (panel not used on any page)`);
                continue;
            }

            // Allow Alpine.js / lazy images to finish rendering
            await delay(600);

            const el = await page.$('#screenshot-output');
            if (!el) {
                console.log(`SKIP — #screenshot-output not found in response`);
                continue;
            }

            await el.screenshot({ path: outputPath, type: 'jpeg', quality: 85 });

            // Dimensions from the rendered element's bounding box
            const box = await el.boundingBox();
            writeMetaFile(outputPath, box.width, box.height);

            // Update blueprint YAML preview: path
            const previewPath  = `${handle}.jpg`;
            const patchedFiles = updateBlueprintPreview(handle, previewPath);

            console.log(`✓  ${handle}.jpg (${Math.round(box.width)}×${Math.round(box.height)})${patchedFiles ? ' · YAML updated' : ''}`);
            succeeded++;

        } catch (err) {
            console.log(`✗  ${err.message}`);
        }
    }

    await browser.close();

    if (succeeded > 0) {
        console.log('\nClearing Statamic stache…');
        try {
            execSync('php please stache:clear', { cwd: ROOT, stdio: 'inherit' });
        } catch {
            console.warn('  stache:clear failed — run it manually if needed.');
        }
    }

    console.log('\nDone!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
