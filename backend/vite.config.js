import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    //Server acessa também com halissonf.interno.senior.com.br
    server: {
        host: '0.0.0.0',
        port: 5173,
        allowedHosts: ['halissonf.interno.senior.com.br', 'localhost', '127.0.0.1']
    }    
});