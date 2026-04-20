/**
 * Copies TinyMCE assets from node_modules to public/js/tinymce/
 * so the editor can be served locally without any CDN dependency.
 *
 * Run automatically via npm postinstall, or manually:
 *   node scripts/copy-tinymce.cjs
 */

const fs   = require('fs');
const path = require('path');

const src  = path.join(__dirname, '..', 'node_modules', 'tinymce');
const dest = path.join(__dirname, '..', 'public', 'js', 'tinymce');

if (!fs.existsSync(src)) {
    console.error('TinyMCE not found in node_modules. Run: npm install tinymce');
    process.exit(1);
}

fs.cpSync(src, dest, { recursive: true, force: true });
console.log('TinyMCE assets copied to public/js/tinymce/');
