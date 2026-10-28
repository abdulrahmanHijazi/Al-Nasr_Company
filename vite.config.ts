import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts', 'resources/css/filament/admin/theme.css'],
            refresh: [
                'app/Http/Controllers/**',
                'routes/**',
                'resources/js/**',
                'resources/views/**/*.blade.php',
            ],
        }),
        tailwindcss(),
        i18n(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
