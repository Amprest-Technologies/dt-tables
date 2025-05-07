import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@node': path.resolve(__dirname, 'node_modules'),
            '@assets': path.resolve(__dirname, 'public'),
            '@vendor': path.resolve(__dirname, 'vendor'),
        }
    }
});