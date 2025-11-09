import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(), // MUST be before laravel plugin for Tailwind v4.0
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        manifest: true, // Generate manifest.json for production (default: true)
        outDir: 'public/build', // Output directory (default for Laravel)
    },
});
