import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
// import inspect from "vite-plugin-inspect";
// import tailwindcss from "@tailwindcss/vite";

/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig(({ command }) => ({
    plugins: [
        // tailwindcss(),
        symfonyPlugin({
        //     stimulus: true,
        //     viteDevServerHostname: "localhost",
        }),
        // inspect(),
    ],
    // base: command === "serve" ? "/" : "/dist/",
    build: {
        manifest: true,
        minify: true,
        rollupOptions: {
            input: {
                app: "./assets/app.js",
            },
        },
    },
    server: {
        host: true,
        port: 5173,
        strictPort: true,
        origin: "https://symfony-pm.ddev.site:5173",
        cors: {
            origin: ["https://symfony-pm.ddev.site"],
        },
    },
}));
