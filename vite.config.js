import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';

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
    server: {
        host: '0.0.0.0',        // Listen on all interfaces (required for Docker)
        port: 5173,
        strictPort: true,
        https: {
            key: fs.readFileSync('./docker/ssl/key.pem'),
            cert: fs.readFileSync('./docker/ssl/cert.pem'),
        },
        hmr: {
            host: 'paradocks.local',  // Browser-accessible hostname
            protocol: 'wss',          // Secure WebSocket for HMR
            clientPort: 8444,         // Match nginx SSL port
        },
        cors: true,  // Enable CORS for cross-origin requests
    },
    build: {
        manifest: 'manifest.json', // Vite 7: force manifest in root build dir (was .vite/manifest.json)
        outDir: 'public/build', // Output directory (default for Laravel)
    },
});
