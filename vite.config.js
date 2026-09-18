import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Dentro de Docker (compose.yaml define BIDA_DOCKER) el servidor tiene que escuchar en todas las
// interfaces para que el navegador del equipo lo alcance, y buscar los cambios por sondeo: los
// avisos del sistema de archivos no cruzan el volumen montado en Windows ni en macOS.
const enDocker = process.env.BIDA_DOCKER === 'true';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: enDocker ? '0.0.0.0' : undefined,
        hmr: enDocker ? { host: 'localhost' } : undefined,
        watch: {
            usePolling: enDocker,
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

