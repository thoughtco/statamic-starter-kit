/**
 * Panel Screenshots Generator
 * Generates screenshot thumbnails for each Statamic replicator panel set.
 *
 * Everything is auto-discovered from the project filesystem — no explicit panel
 * list required:
 *
 *   1. Scans resources/views/_panels/*.antlers.html for panel handles.
 *   2. Reads each template to extract the CSS selector from the outer <section>.
 *   3. Scans content/collections/**\/*.md to find the first page that uses each panel.
 *   4. Determines the DOM index for panels that share a selector on the same page.
 *   5. Loads the source page in one browser tab, extracts just that panel's HTML.
 *   6. Renders an isolated dummy page (only that panel + compiled CSS/JS) in a
 *      second tab and screenshots the <section> element.
 *   7. Writes the preview path back into any blueprint YAML that references the
 *      panel set handle.
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
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname  = path.dirname(__filename);
const ROOT       = path.join(__dirname, '..');

const BASE_URL   = (process.argv[2] || readEnvUrl() || 'http://localhost:8000').replace(/\/$/, '');
const ONLY       = process.argv[3] ?? null;

/** Read APP_URL from the project's .env file, stripping surrounding quotes. */
function readEnvUrl() {
    const envFile = path.join(ROOT, '.env');
    if (!fs.existsSync(envFile)) return null;
    const match = fs.readFileSync(envFile, 'utf8').match(/^APP_URL=["']?([^"'\r\n]+)["']?/m);
    return match ? match[1].trim() : null;
}

const PANELS_DIR   = path.join(ROOT, 'resources', 'views', '_panels');
const CONTENT_DIR  = path.join(ROOT, 'content', 'collections');
const BLUEPRINT_DIR = path.join(ROOT, 'resources', 'blueprints');
const OUTPUT_DIR   = path.join(ROOT, 'public', 'assets', 'files', 'panel-screenshots');

// Relative path stored in blueprint YAML (relative to assets/files/)
const YAML_PREVIEW_BASE = 'assets/files/panel-screenshots';

// ---------------------------------------------------------------------------
// Discovery helpers
// ---------------------------------------------------------------------------

/**
 * Return all panel handles found in resources/views/_panels/.
 * Handle = filename without the .antlers.html extension.
 */
function discoverHandles() {
    return fs.readdirSync(PANELS_DIR)
        .filter(f => f.endsWith('.antlers.html'))
        .map(f => f.replace(/\.antlers\.html$/, ''))
        .sort();
}

/**
 * Read the panel's Antlers template and extract the first CSS class from the
 * outer <section> element, e.g. `<section class="blog-listing px-6 …">` → `.blog-listing`
 */
function selectorFromTemplate(handle) {
    const file = path.join(PANELS_DIR, `${handle}.antlers.html`);
    const src  = fs.readFileSync(file, 'utf8');
    const match = src.match(/<section[^>]+class="([^"]+)"/);
    if (!match) return null;
    const firstClass = match[1].trim().split(/\s+/)[0];
    return `.${firstClass}`;
}

/**
 * Scan all .md content files under content/collections/ and return a map of
 * handle → { file, url, rawTypes }
 *
 * rawTypes is the ordered list of all panel `type:` values found in that file,
 * used later to compute the DOM index for duplicate selectors.
 */
function discoverContentMap() {
    const result = {}; // handle → { file, url, rawTypes }

    const mdFiles = findFiles(CONTENT_DIR, f => f.endsWith('.md'));

    for (const file of mdFiles) {
        const src = fs.readFileSync(file, 'utf8');

        // Collect all panel types in document order
        const typeMatches = [...src.matchAll(/^\s+type:\s+(\S+)\s*$/gm)];
        const rawTypes = typeMatches.map(m => m[1]);
        if (rawTypes.length === 0) continue;

        // Derive the URL from the `slug:` field in the front matter
        const slugMatch = src.match(/^slug:\s*(.+)$/m);
        let slug = slugMatch ? slugMatch[1].trim() : path.basename(file, '.md');
        const url = slug.startsWith('/') ? BASE_URL + slug : `${BASE_URL}/${slug}`;

        for (const handle of rawTypes) {
            if (!result[handle]) {
                result[handle] = { file, url, rawTypes };
            }
        }
    }

    return result;
}

/**
 * Given a handle, its selector, and the ordered list of panel types on the
 * chosen source page, compute which index (0-based) this handle occupies
 * among panels that share the same CSS selector.
 */
function computeIndex(handle, selector, rawTypes) {
    let idx = 0;
    for (const type of rawTypes) {
        if (type === handle) return idx;
        if (selectorFromTemplate(type) === selector) idx++;
    }
    return 0; // fallback
}

// ---------------------------------------------------------------------------
// YAML update helper
// ---------------------------------------------------------------------------

/**
 * Search all blueprint YAML files for a block matching `{handle}:` that
 * contains a `display:` key (characteristic of a replicator set entry) and
 * add or replace its `preview:` line.
 *
 * Works by line-by-line analysis so it preserves all other formatting/comments.
 */
function updateBlueprintPreview(handle, previewPath) {
    const yamlFiles = findFiles(BLUEPRINT_DIR, f => f.endsWith('.yaml') || f.endsWith('.yml'));
    let updated = 0;

    for (const file of yamlFiles) {
        const original = fs.readFileSync(file, 'utf8');
        const result   = patchYamlPreview(original, handle, previewPath);

        if (result !== original) {
            fs.writeFileSync(file, result, 'utf8');
            updated++;
        }
    }

    return updated;
}

/**
 * Patch a YAML string in-place:
 *  - Finds the line `{indent}{handle}:\n`
 *  - Verifies it's a replicator set block (contains `display:`)
 *  - Replaces or inserts `preview: {previewPath}` within that block
 */
function patchYamlPreview(src, handle, previewPath) {
    const lines  = src.split('\n');
    const out    = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];
        // Match a YAML key that is exactly the handle, possibly with leading spaces
        const keyMatch = line.match(/^(\s+)([\w]+):\s*$/);

        if (keyMatch && keyMatch[2] === handle) {
            const baseIndent = keyMatch[1];
            const propIndent = baseIndent + '  ';

            // Collect this block (all lines indented deeper than baseIndent)
            const blockStart = i;
            i++;
            const blockLines = [];

            while (i < lines.length) {
                const bl = lines[i];
                // A blank line or a line that is not more-indented ends the block
                if (bl.trim() === '' || (bl.trim() !== '' && !bl.startsWith(baseIndent + ' '))) break;
                blockLines.push(bl);
                i++;
            }

            // Only patch blocks that look like replicator set entries
            const hasDisplay = blockLines.some(l => l.trim().startsWith('display:'));
            if (!hasDisplay) {
                out.push(line, ...blockLines);
                continue;
            }

            const previewLine   = `${propIndent}preview: ${previewPath}`;
            const existingIdx   = blockLines.findIndex(l => /^\s+preview:/.test(l));
            const fieldsIdx     = blockLines.findIndex(l => l.trim().startsWith('fields:'));

            if (existingIdx >= 0) {
                blockLines[existingIdx] = previewLine;
            } else {
                // Insert before `fields:`, or at the end of the block
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
// Isolated page builder
// ---------------------------------------------------------------------------

/**
 * Build a minimal standalone HTML page containing only the panel markup.
 * Root-relative URLs are converted to absolute so images/fonts resolve when
 * the page is rendered outside the site's URL context.
 */
function buildIsolatedPage(panelHTML, assets, baseUrl) {
    const html = absolutifyUrls(panelHTML, baseUrl);

    const styleLinks = assets.css
        .map(href => `<link rel="stylesheet" href="${href}">`)
        .join('\n');

    const scriptTags = assets.js
        .map(src => `<script type="module" src="${src}"></script>`)
        .join('\n');

    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
${styleLinks}
<style>
  *, *::before, *::after { box-sizing: border-box; }
  body { margin: 0; padding: 0; }
  [x-cloak] { display: none !important; }
</style>
</head>
<body>
${html}
${scriptTags}
</body>
</html>`;
}

/**
 * Convert root-relative URLs in HTML attribute values to absolute.
 * Handles src, href, action, and srcset attributes.
 */
function absolutifyUrls(html, baseUrl) {
    const base = baseUrl.replace(/\/$/, '');
    return html
        .replace(/\b(src|href|action)="(\/[^"]*)"/g,  `$1="${base}$2"`)
        .replace(/\b(src|href|action)='(\/[^']*)'/g,  `$1='${base}$2'`)
        .replace(/\bsrcset="([^"]*)"/g, (_, s) =>
            `srcset="${s.replace(/((?:^|,\s*))(\/[^\s,]+)/g, `$1${base}$2`)}"`)
        .replace(/\bsrcset='([^']*)'/g, (_, s) =>
            `srcset='${s.replace(/((?:^|,\s*))(\/[^\s,]+)/g, `$1${base}$2`)}'`);
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
    // 1. Discover all panel handles from the _panels directory
    const allHandles = discoverHandles();
    const handles = ONLY ? allHandles.filter(h => h === ONLY) : allHandles;

    if (ONLY && handles.length === 0) {
        console.error(`Unknown panel handle: "${ONLY}"`);
        console.error(`Discovered handles:\n  ${allHandles.join('\n  ')}`);
        process.exit(1);
    }

    // 2. Build selector map from templates
    const selectors = {};
    for (const handle of handles) {
        selectors[handle] = selectorFromTemplate(handle);
    }

    // 3. Find which page URL contains each panel
    const contentMap = discoverContentMap();

    // 4. Assemble the full panel config
    const panels = [];
    const skipped = [];

    for (const handle of handles) {
        const selector = selectors[handle];
        if (!selector) {
            skipped.push(`${handle} (no <section> found in template)`);
            continue;
        }

        const content = contentMap[handle];
        if (!content) {
            skipped.push(`${handle} (not used on any content page)`);
            continue;
        }

        const index = computeIndex(handle, selector, content.rawTypes);
        panels.push({ handle, url: content.url, selector, index });
    }

    if (skipped.length) {
        console.warn(`Skipping ${skipped.length} panel(s):`);
        skipped.forEach(s => console.warn(`  ✗ ${s}`));
        console.warn('');
    }

    if (panels.length === 0) {
        console.error('No panels to screenshot.');
        process.exit(1);
    }

    fs.mkdirSync(OUTPUT_DIR, { recursive: true });

    console.log(`Panel Screenshots — ${panels.length} panel${panels.length !== 1 ? 's' : ''}`);
    console.log(`Base URL : ${BASE_URL}`);
    console.log(`Output   : ${OUTPUT_DIR}\n`);

    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });

    // Two tabs:
    //   sourcePage  — loads the live site once per unique URL to get server-rendered HTML
    //   previewPage — renders the isolated dummy page and takes the screenshot
    const sourcePage  = await browser.newPage();
    const previewPage = await browser.newPage();

    await sourcePage.setViewport({ width: 1440, height: 900 });
    await previewPage.setViewport({ width: 1440, height: 900 });
    previewPage.on('console', () => {}); // suppress noise

    // Group panels by source URL so we only load each page once
    const byUrl = new Map();
    for (const panel of panels) {
        if (!byUrl.has(panel.url)) byUrl.set(panel.url, []);
        byUrl.get(panel.url).push(panel);
    }

    // Vite bundles the same CSS/JS on every page — cache after the first load
    let assets = null;

    for (const [url, group] of byUrl) {
        console.log(`  Loading ${url}`);

        try {
            await sourcePage.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
        } catch (err) {
            console.warn(`  ✗ Could not load ${url}: ${err.message}`);
            group.forEach(p => console.log(`    SKIP [${p.handle}]`));
            continue;
        }

        // Let lazy content / Alpine.js finish its initial render
        await delay(800);

        // Grab all compiled CSS and JS URLs from the page <head> once
        if (!assets) {
            assets = await sourcePage.evaluate(() => ({
                css: [...document.querySelectorAll('link[rel="stylesheet"]')].map(l => l.href),
                js:  [...document.querySelectorAll('script[type="module"][src]')].map(s => s.src),
            }));
        }

        for (const { handle, selector, index } of group) {
            process.stdout.write(`  [${handle}] `);

            try {
                // Extract the server-rendered HTML for this panel only
                const panelHTML = await sourcePage.evaluate((sel, idx) => {
                    const el = document.querySelectorAll(sel)[idx];
                    return el ? el.outerHTML : null;
                }, selector, index);

                if (!panelHTML) {
                    const count = await sourcePage.$$eval(selector, els => els.length);
                    console.log(`SKIP — "${selector}" not found (${count} match${count !== 1 ? 'es' : ''} on page, wanted index ${index})`);
                    continue;
                }

                // Build and render a minimal page containing only this panel
                const isolated = buildIsolatedPage(panelHTML, assets, BASE_URL);
                await previewPage.setContent(isolated, { waitUntil: 'networkidle2', timeout: 30000 });
                await delay(500);

                // Screenshot just the <section> element — the only panel in the page
                const el = await previewPage.$('section');
                if (!el) {
                    console.log('SKIP — no <section> found in isolated page');
                    continue;
                }

                const outputPath = path.join(OUTPUT_DIR, `${handle}.jpg`);
                await el.screenshot({ path: outputPath, type: 'jpeg', quality: 85 });

                // Write the preview path back into any blueprint YAML that references this handle
                const previewYamlPath = `${YAML_PREVIEW_BASE}/${handle}.jpg`;
                const patchedFiles = updateBlueprintPreview(handle, previewYamlPath);

                console.log(`✓  ${handle}.jpg${patchedFiles ? ` (YAML updated)` : ''}`);

            } catch (err) {
                console.log(`✗  ${err.message}`);
            }
        }
    }

    await browser.close();
    console.log('\nDone!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
