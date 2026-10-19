import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ isSsrBuild }) => ({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
    ],
    server: {
        cors: true,
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
    },
    build: {
        rollupOptions: {
            output: !isSsrBuild ? {
                manualChunks: {
                    // Split vendor code into separate chunks (client-side only)
                    'vue-vendor': ['vue', '@inertiajs/vue3'],
                    'ui-vendor': ['@heroicons/vue', 'swiper'],
                    'utils': ['lodash', 'axios'],
                },
            } : undefined,
        },
        chunkSizeWarningLimit: 500,
    },
}));
