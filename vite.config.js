import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.scss",
                "resources/js/app.js",
                "resources/js/product-table.js",
                "resources/js/search-result.js",
                "resources/js/accounts-page.js",
                "resources/js/nostock-page.js",
                "resources/js/lowstock-page.js",
                "resources/js/log-page.js",
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
