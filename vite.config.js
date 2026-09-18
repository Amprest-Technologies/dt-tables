import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/admin.js',
                'resources/js/client.js',
                'resources/css/admin.css',
                'resources/css/client.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            'eta': path.resolve(import.meta.dirname, 'node_modules/eta/dist/core.js'),
            '@resources': path.resolve(import.meta.dirname, 'resources'),
            '@node': path.resolve(import.meta.dirname, 'node_modules'),
            '@assets': path.resolve(import.meta.dirname, 'public'),
            '@vendor': path.resolve(import.meta.dirname, 'vendor'),
        }
    }
});