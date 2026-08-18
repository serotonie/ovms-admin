import { defineConfig } from 'vite';
import inertia from '@inertiajs/vite'
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: {
            origin: [
                'https://vehicules.theleders.family',
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                'http://localhost:8000',
            ],
            methods: ['GET', 'HEAD', 'PUT', 'PATCH', 'POST', 'DELETE'],
            credentials: true,
        },
        hmr: {
            host: 'localhost',
            port: 5173,
            protocol: 'ws',
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({ autoImport: { labs: true } }),
        inertia({
            ssr: false,
        }),
    ]
});
