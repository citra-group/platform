import Vue from "@vitejs/plugin-vue";
import Vuetify from "vite-plugin-vuetify";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import { fileURLToPath, URL } from "node:url";
import { VitePWA } from "vite-plugin-pwa";
import { viteStaticCopy } from "vite-plugin-static-copy";

export default defineConfig({
    css: {
        preprocessorOptions: {
            scss: {
                api: "modern", // or "modern"
                silenceDeprecations: ["legacy-js-api"],
            },
        },
    },
    plugins: [
        laravel({
            input: ["resources/src/desktop.js", "resources/src/mobile.js"],
            refresh: true,
            detectTls: "citrapp.test",
        }),
        Vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        Vuetify({
            autoImport: true,
        }),
        viteStaticCopy({
            targets: [
                {
                    src: "public/assets/pwa-192x192.png",
                    dest: "assets",
                },
            ],
        }),
        VitePWA({
            devOptions: {
                enabled: false,
                type: "module",
            },

            strategies: "injectManifest",
            srcDir: "resources/src/workers",
            filename: "service-worker.js",

            registerType: "autoUpdate",

            manifest: {
                name: "Citrapp",
                short_name: "Citrapp",
                description: "Citra Application Platform",
                icons: [
                    {
                        src: "assets/pwa-64x64.png",
                        sizes: "64x64",
                        type: "image/png",
                    },
                    {
                        src: "assets/pwa-192x192.png",
                        sizes: "192x192",
                        type: "image/png",
                    },
                    {
                        src: "assets/pwa-512x512.png",
                        sizes: "512x512",
                        type: "image/png",
                    },
                    {
                        src: "assets/maskable-icon-512x512.png",
                        sizes: "512x512",
                        type: "image/png",
                        purpose: "maskable",
                    },
                ],
                start_url: "/build/",
                display: "fullscreen",
                display_override: ["fullscreen", "minimal-ui"],
                background_color: "#5A6062",
                theme_color: "#5A6062",
            },

            workbox: {
                exclude: [/\.(?:png|php|jpg|jpeg|svg|txt|ico|html|htaccess)$/],
                maximumFileSizeToCacheInBytes: 10485760,
            },
        }),
    ],
    define: { "process.env": {} },
    resolve: {
        alias: {
            "@components": fileURLToPath(
                new URL("./resources/src/components", import.meta.url)
            ),
            "@modules": fileURLToPath(new URL("./modules", import.meta.url)),
            "@plugins": fileURLToPath(
                new URL("./resources/src/plugins", import.meta.url)
            ),
            "@pinia": fileURLToPath(
                new URL("./resources/src/plugins/pinia", import.meta.url)
            ),
            "@styles": fileURLToPath(
                new URL("./resources/src/styles", import.meta.url)
            ),
        },
        extensions: [".js", ".json", ".jsx", ".mjs", ".ts", ".tsx", ".vue"],
    },
});
