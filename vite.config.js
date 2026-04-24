import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: [
                ...refreshPaths,
                "app/Livewire/**",
                "app/Filament/**",
                "app/Providers/**",
            ],
        }),
    ],
    build: {
        // Optimize chunk size
        chunkSizeWarningLimit: 1000,
        // Enable minification
        minify: 'esbuild',
        // Generate source maps only in development
        sourcemap: false,
        // CSS code splitting
        cssCodeSplit: true,
    },
});
