import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Cada tarjeta estacional carga su hoja solo en su plantilla (resources/css/cards)
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/cards/amor.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Aislar vendors pesados en chunks separados y predecibles
                    if (id.includes('node_modules/video.js'))   return 'vendor-videojs';
                    if (id.includes('node_modules/lottie-web')) return 'vendor-lottie';
                    if (id.includes('node_modules/motion'))     return 'vendor-motion';
                },
            },
        },
    },
});

