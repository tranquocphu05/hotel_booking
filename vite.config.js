import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/booking.css',
                'resources/css/contact-form.css',
                'resources/js/app.js',
                'resources/js/booking.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Code splitting for better caching
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs'],
                    'utils': ['axios'],
                },
            },
        },
        // Optimize chunk size
        chunkSizeWarningLimit: 600,
        // Minification - use esbuild (faster and built-in)
        minify: 'esbuild',
        // Source maps for production debugging (optional)
        sourcemap: false,
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Optimize asset inlining threshold
        assetsInlineLimit: 4096,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['alpinejs', 'axios'],
    },
    // Server optimization for development
    server: {
        hmr: {
            overlay: true,
        },
    },
});
