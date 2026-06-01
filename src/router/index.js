import { createRouter, createWebHashHistory } from "vue-router"
import DownloadView from "@/views/DownloadView.vue"

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: "/",
            name: "download",
            component: DownloadView,
        },
        {
            path: "/server",
            name: "server",
            component: () => import("@/views/ServerView.vue"),
        },
        {
            path: "/search",
            name: "search",
            component: () => import("@/views/SearchView.vue"),
        },
        {
            path: "/ed2k",
            name: "ed2k",
            component: () => import("@/views/Ed2kLinkView.vue"),
        },
        {
            path: "/prefs",
            name: "prefs",
            component: () => import("@/views/PrefsView.vue"),
        },
        {
            path: "/logs",
            name: "logs",
            component: () => import("@/views/LogsView.vue"),
        },
        {
            path: "/about",
            name: "about",
            // route level code-splitting
            // this generates a separate chunk (About.[hash].js) for this route
            // which is lazy-loaded when the route is visited.
            component: () => import("@/views/AboutView.vue"),
        },
    ],
})

export default router
