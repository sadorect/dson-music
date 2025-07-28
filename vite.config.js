import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                'resources/css/dson-theme.css', 
                'resources/js/app.js',
                'resources/js/search.js',
                'resources/js/comments.js',
            'resources/js/logo-preview.js'
        ],
                
            refresh: true,
        }),
    ],
     server: {
    watch: {
      ignored: [
        '**/vendor/**',
        '**/node_modules/**',
        '**/node_modules/**',
        '**/.git/**',
        '**/.gitignore/**',
        // add other heavy dirs like storage, node_modules, etc.
      ],
    },
  },
});
