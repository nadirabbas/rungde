import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
    plugins: [
        laravel({
            input: ['client/main.ts'],
            refresh: true,
        }),
        vue(),
        VitePWA({
            name: 'Rung de',
            short_name: 'Rung de',
            orientation: 'landscape',
            scope: '/',
            id: '/',
            start_url: '/',
            buildBase: '/',
            outDir: 'public',
            registerType: 'autoUpdate', manifest: {
                theme_color: '#004e92',
                orientation: 'landscape',
                start_url: '/',
                display: 'fullscreen',
                icons: [
                    {
                        src: '/images/icon.jpg',
                        sizes: '1000x1000',
                        type: 'image/jpeg',
                        purpose: 'any'
                    },
                ]
            }
        })
    ],
});
