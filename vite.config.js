import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/client/theme.css',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Filament/**',
                'app/Forms/Components/**',
                'app/Livewire/**',
                'app/Infolists/Components/**',
                'app/Providers/Filament/**',
                'app/Tables/Columns/**',
            ],
        }),
    ],
    server: {
        host: '0.0.0.0',
        watch: {
            usePolling: true,  // Esto mejora la detección de cambios en Docker
        },
    },
});
