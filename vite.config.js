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
            '@resources': path.resolve(__dirname, 'resources'),
            '@node': path.resolve(__dirname, 'node_modules'),
            '@assets': path.resolve(__dirname, 'public'),
            '@vendor': path.resolve(__dirname, 'vendor'),
        }
    }
});