import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/common.css',
                'resources/css/edit.css',
                'resources/css/flash-message.css',
                'resources/css/index.css',
                'resources/css/item_detail.css',
                'resources/css/item_sell.css',
                'resources/css/login.css',
                'resources/css/mypage.css',
                'resources/css/purchase.css',
                'resources/css/purchase_address.css',
                'resources/css/register.css',
                'resources/css/verify.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
