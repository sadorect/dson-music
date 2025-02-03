import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                'resources/css/dson-theme.css', 
                'resources/js/app.js',
                'resources/js/search.js',
                'resources/js/comments.js'],
            refresh: true,
        }),
    ],
});
