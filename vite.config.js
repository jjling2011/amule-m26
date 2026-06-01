import { fileURLToPath, URL } from "node:url"

import { defineConfig } from "vite"
import vue from "@vitejs/plugin-vue"
import vueDevTools from "vite-plugin-vue-devtools"
import { resolve } from "path"

// https://vite.dev/config/
export default defineConfig({
    plugins: [vue(), vueDevTools()],
    base: "/",
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./src", import.meta.url)),
        },
    },
    build: {
        // 1. 指定输出目录为 dist/p01
        outDir: "dist/M26",
        // 2. 每次打包前清空输出目录
        emptyOutDir: true,
        rollupOptions: {
            input: {
                m26: resolve(__dirname, "m26.html"),
            },
            output: {
                // Prevents code-splitting into multiple chunks
                manualChunks: undefined,
                // Ensures all dynamic imports are inlined into a single file
                codeSplitting: false,
                // 3. 入口 JS 文件直接输出到根目录，不加子文件夹
                entryFileNames: "[name].js",
                // 4. 分包/懒加载的 JS 文件直接输出到根目录
                chunkFileNames: "[name].js",
                // 5. 所有静态资源（CSS、图片、字体等）直接输出到根目录
                assetFileNames: "[name][extname]",
            },
        },
    },
    server: {
        port: 5173,
        open: false,
        proxy: {
            "/m26test.php": {
                target: "http://127.0.0.1:4401", // 替换成你真实的 PHP 后端地址
                changeOrigin: true, // 允许跨域
                // 通过 configure 添加监听器
                configure: (proxy, options) => {
                    proxy.on("proxyReq", (proxyReq, req, res) => {
                        // 移除可能导致 CORS 预检失败的 Origin 头
                        // proxyReq.removeHeader("Origin")
                        // 如果还遇到其他问题，也可以尝试移除 Referer 头
                        // proxyReq.removeHeader('referer');
                    })
                },
            },
            "/serv.php": {
                target: "http://127.0.0.1:4401", // 替换成你真实的 PHP 后端地址
                changeOrigin: true, // 允许跨域
                // 通过 configure 添加监听器
                configure: (proxy, options) => {
                    proxy.on("proxyReq", (proxyReq, req, res) => {
                        // 移除可能导致 CORS 预检失败的 Origin 头
                        // proxyReq.removeHeader("Origin")
                        // 如果还遇到其他问题，也可以尝试移除 Referer 头
                        // proxyReq.removeHeader('referer');
                    })
                },
            },
            "/login.php": {
                target: "http://127.0.0.1:4401", // 替换成你真实的 PHP 后端地址
                changeOrigin: true, // 允许跨域
                // 如果目标服务器使用自签名 HTTPS 证书，忽略证书验证（开发环境使用）
                // secure: false,
                // 如果后端接口不需要 /api 前缀，可以将其去掉
                // rewrite: (path) => path.replace(/^\/api/, '')
            },
        },
    },
})
