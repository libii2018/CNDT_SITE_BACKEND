import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';

// Dashboard React intégré au pipeline Vite de Laravel :
//   - entrée : resources/js/dashboard/main.tsx
//   - build  : public/build (manifest lu par la directive @vite)
//   - servi  : resources/views/admin.blade.php (route /admin)
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/dashboard/main.tsx'],
            refresh: false,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js/dashboard'),
        },
    },
});
