import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'node:fs';
import path from 'node:path';

function normalizeLaravelManifest() {
    return {
        name: 'normalize-laravel-manifest',
        closeBundle() {
            const manifestPath = path.resolve('public/build/manifest.json');

            if (!fs.existsSync(manifestPath)) {
                return;
            }

            const root = `${process.cwd().replaceAll('\\', '/')}/`;
            const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
            const normalized = Object.fromEntries(
                Object.entries(manifest).map(([key, value]) => [
                    key.replace(root, ''),
                    {
                        ...value,
                        src: value.src?.replace(root, ''),
                    },
                ]),
            );

            fs.writeFileSync(manifestPath, `${JSON.stringify(normalized, null, 2)}\n`);
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        normalizeLaravelManifest(),
    ],
});
