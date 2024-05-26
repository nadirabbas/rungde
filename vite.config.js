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
            scope: '/',
            buildBase: '/',
            outDir: 'public',
            registerType: 'autoUpdate',
            manifest: {
                id: '/',
                scope: '/',
                description: 'Court piece multiplayer!',
                name: 'Rung de',
                short_name: 'Rung de',
                theme_color: '#004e92',
                orientation: 'landscape',
                start_url: '/',
                display: 'fullscreen',
                icons: [
                    {
                        src: '/images/icon.png',
                        sizes: '1000x1000',
                        type: 'image/png',
                        purpose: 'any'
                    },
                ],
                screenshots: [
                    {
                        src: '/images/screenshots/game.png',
                        sizes: '838x375',
                        type: 'image/png',
                        form_factor: 'wide',
                        label: 'Rung.de game session'
                    },
                    {
                        src: '/images/screenshots/game-narrow.png',
                        sizes: '885x880',
                        type: 'image/png',
                        form_factor: 'narrow',
                        label: 'Rung.de game session'
                    }
                ]
            }
        })
    ],
});
