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
                start_url: '/',
                icons: [
                    {
                        src: '/images/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/images/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    },
                    {
                        src: '/images/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any'
                    },
                    {
                        src: '/images/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable'
                    }
                ]
            }
        })
    ],
});
