<script setup>
import { ref, onMounted, onUnmounted } from "vue"
import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"
import { setServAddr } from "@/libs/share-states.js"

// 获取 i18n 的 t 函数和 locale 对象
const { t } = useI18n()

const stats = ref({ id: 0xffffffff })

function getSpeed() {
    const up = stats.value?.speed_up || 0
    const down = stats.value?.speed_down || 0
    // Speed: 123 K, 456 K
    return `${utils.formatBytes(up)}, ${utils.formatBytes(down)}`
}

function getServStats() {
    const s = stats.value
    if (!s || !s.serv_name) {
        return "Offline!"
    }
    return `${s.serv_name}`
}

function getUserID() {
    const s = stats.value
    if (!s || !s.id) {
        return "Not connected"
    } else if (s.id === 0xffffffff) {
        return "Connecting..."
    }
    return s.id < 16777216 ? `(low) ${s.id}` : `(high) ${s.id}`
}

function getKadStats() {
    const s = stats.value
    if (!s || !s.kad_connected) {
        return "Offline!"
    }
    return s.kad_firewalled ? "Firewalled" : "Connected"
}

let handle = null
function watchServerState() {
    const delay = 3 * 1000
    async function checkServ() {
        try {
            const resp = await utils.query({
                cmd: "GetStats",
            })
            stats.value = resp.data
            setServAddr(stats.value?.serv_addr || "")
        } catch (err) {
            utils.log(`[sidebar] watch server state error: ${err.message}`)
        } finally {
            handle = setTimeout(checkServ, delay)
        }
    }
    handle = setTimeout(checkServ, delay)
}

const emit = defineEmits(["onAppNameClick"])
function onAppNameClick() {
    emit("onAppNameClick", {})
}

onUnmounted(function () {
    clearTimeout(handle)
})

onMounted(function () {
    if (utils.isDevEnv()) {
        document.title = "M26 DEV"
    }
    const curTheme = utils.getCurThemeMode()
    utils.switchTheme(curTheme)
    window.dialogs.updateText(t("dialogs.ok"), t("dialogs.cancel"))
    watchServerState()
})
</script>

<template>
    <div class="side-bar">
        <div class="logo-bar" @click="onAppNameClick()" style="cursor: pointer">
            <img src="/favicon.ico" alt="logo.ico" />
            <span>Amule M26</span>
        </div>
        <nav class="nav-bar">
            <RouterLink to="/">
                <i class="fa fa-download" aria-hidden="true" :title="t('nav.download')"></i>
                <span class="nav-text">{{ t("nav.download") }}</span>
            </RouterLink>
            <RouterLink to="/search">
                <i class="fa fa-search" aria-hidden="true" :title="t('nav.search')"></i>
                <span class="nav-text">{{ t("nav.search") }}</span>
            </RouterLink>
            <RouterLink to="/server">
                <i class="fa fa-server" aria-hidden="true" :title="t('nav.server')"></i>
                <span class="nav-text">{{ t("nav.server") }}</span>
            </RouterLink>
            <RouterLink to="/ed2k">
                <i class="fa fa-link" aria-hidden="true" title="ED2K"></i>
                <span class="nav-text">ED2K</span>
            </RouterLink>
            <RouterLink to="/logs">
                <i class="fa fa-book" aria-hidden="true" :title="t('nav.logs')"></i>
                <span class="nav-text">{{ t("nav.logs") }}</span>
            </RouterLink>
            <RouterLink to="/prefs">
                <i class="fa fa-cog" aria-hidden="true" :title="t('nav.prefs')"></i>
                <span class="nav-text">{{ t("nav.prefs") }}</span>
            </RouterLink>
            <RouterLink to="/about">
                <i class="fa fa-info-circle" aria-hidden="true" :title="t('nav.about')"></i>
                <span class="nav-text">{{ t("nav.about") }}</span>
            </RouterLink>
        </nav>
        <div class="serv-stats">
            <span>{{ t("nav.speed") }}:</span>
            <span :title="t('nav.speed_tip')">{{ getSpeed() }}</span>
        </div>
        <div class="serv-stats">
            <span>UID:</span>
            <span :title="getUserID()">{{ getUserID() }}</span>
        </div>
        <div class="serv-stats">
            <span>KAD:</span>
            <span>{{ getKadStats() }}</span>
        </div>
        <div class="serv-stats" style="margin-bottom: 1rem">
            <span>{{ t("nav.server") }}:</span>
            <span :title="getServStats()">{{ getServStats() }}</span>
        </div>
    </div>
</template>

<style scoped>
.serv-stats {
    display: flex;
    flex-direction: row;
    font-size: 0.9rem;
}

.serv-stats span {
    color: var(--sidebar-fg-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.serv-stats span:nth-child(1) {
    width: 4rem;
    margin-right: 0.5rem;
    text-align: right;
}

.serv-stats span:nth-child(2) {
    width: 9rem;
    text-align: left;
}

.side-bar {
    width: 14rem;
    position: fixed;
    height: 100%;
    z-index: 100;
    display: flex;
    flex-direction: column;
    background-color: var(--sidebar-bg-color);
}

.side-bar * {
    background-color: var(--sidebar-bg-color);
    color: var(--sidebar-fg-color);
}

.logo-bar {
    display: flex;
    margin: 0.5rem;
    margin-bottom: 1rem;
}

.logo-bar img {
    width: 3rem;
    height: 2.5rem;
    display: inline-block;
    margin-right: 1rem;
}

.logo-bar span {
    width: 10rem;
    margin-top: 0.25rem;
    font-size: 1.5rem;
    color: var(--sidebar-fg-color);
    display: inline-block;
}

.nav-bar {
    flex-grow: 1;
    margin: 0.5rem;
}

.nav-text {
    font-size: 1.25rem;
}

.nav-bar a {
    width: 83%;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    display: block;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    font-size: 1.25rem;
}

.nav-bar a.router-link-exact-active,
.nav-bar a.router-link-exact-active * {
    background-color: var(--sidebar-route-exact);
}

.nav-bar a:hover,
.nav-bar a:hover * {
    background-color: var(--sidebar-route-hover);
}

.nav-bar a * {
    margin-right: 0.5rem;
}

.side-bar-bottom {
    flex-direction: column;
    margin: 0.5rem;
}

@media (max-height: 550) {
    .nav-bar a {
        margin-bottom: 0.25rem;
        padding: 0.25rem 1rem;
    }
}

@media (max-width: 1080px) {
    .side-bar {
        width: 2.5rem;
    }

    .logo-bar img {
        margin-left: 0.2rem;
    }

    .serv-stats,
    .logo-bar span,
    .nav-bar a .nav-text,
    .side-bar-bottom {
        display: none;
    }
}
</style>
