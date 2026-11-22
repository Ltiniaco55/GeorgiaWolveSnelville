import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                local('Inter', {
                    alias: 'gw-inter',
                    variable: '--gw-font-inter',
                    variants: [
                        { src: 'resources/fonts/inter/inter-latin-400-normal.woff2', weight: 400 },
                        { src: 'resources/fonts/inter/inter-latin-500-normal.woff2', weight: 500 },
                        { src: 'resources/fonts/inter/inter-latin-600-normal.woff2', weight: 600 },
                    ],
                    display: 'swap',
                    preload: [{ weight: 400 }, { weight: 600 }],
                    fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
                }),
                local('Barlow Condensed', {
                    alias: 'gw-barlow',
                    variable: '--gw-font-barlow',
                    variants: [
                        { src: 'resources/fonts/barlow-condensed/barlow-condensed-latin-700-normal.woff2', weight: 700 },
                        { src: 'resources/fonts/barlow-condensed/barlow-condensed-latin-800-normal.woff2', weight: 800 },
                    ],
                    display: 'swap',
                    preload: [{ weight: 800 }],
                    fallbacks: ['Impact', 'Arial Narrow', 'sans-serif'],
                }),
            ],
        }),
        inertia({ ssr: false }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
