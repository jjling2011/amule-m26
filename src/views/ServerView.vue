<script setup>
import { onMounted, onUnmounted, watch, ref, computed } from "vue"
import DropdownButton from "@/widgets/DropdownButton.vue"

import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"
import compos from "@/libs/compos.js"
import { store } from "@/libs/share-states.js"

const { t } = useI18n()

const servers = ref([])
const curServ = ref("")

function isConnected(address) {
    const cur = curServ.value
    return cur.indexOf(address) === 1
}

watch(
    () => store.servAddr,
    (newAddr) => (curServ.value = newAddr),
)

async function connectServer(ip, port) {
    try {
        utils.log(`try to connect: [${ip}:${port}]`)
        await utils.query({ cmd: "ConnectServer", ip: ip, port: port })
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function disconnectServer() {
    try {
        await utils.query({ cmd: "DisconnectServer" })
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function removeServer(ip, port) {
    const msg = t("servers.confirmRemoveServer")
    const ok = await window.dialogs.confirm(msg)
    if (!ok) {
        return
    }

    try {
        utils.log(`try to remove: [${ip}:${port}]`)
        await utils.query({ cmd: "RemoveServer", ip, port })
        refreshUI()
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function refreshUI() {
    try {
        const response = await utils.query({ cmd: "GetServers" })
        servers.value = response.data
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

const sortTag = compos.useLocalStorage("m26-server-sort-tag", "files")
const sortOrder = compos.useLocalStorage("m26-server-sort-order", "descending")

function getOrderSign(name) {
    if (name !== sortTag.value) {
        return ""
    }
    return sortOrder.value === "descending" ? " ↓" : " ↑"
}

function switchSortKeyTo(key) {
    if (sortTag.value === key) {
        sortOrder.value = sortOrder.value === "descending" ? "ascending" : "descending"
    } else {
        sortTag.value = key
    }
}

const sortedServers = computed(() => {
    const r = [...servers.value]
    const sortKey = sortTag.value || "files"
    const isNumKey = sortKey !== "name"
    utils.sortInPlace(r, sortKey, isNumKey, sortOrder.value === "descending")
    return r
})

const new_server_param = ref("")
async function addNewServer() {
    const ps = (new_server_param.value || "").split(/[: ]/)
    if (ps.length < 3) {
        window.dialogs.alert(t("servers.require_three_param"))
        return
    }

    const ip = ps[0]
    const port = ps[1]
    if (!utils.isIPv4Addr(`${ip}:${port}`)) {
        window.dialogs.alert(t("servers.invalid_ipv4"))
        return
    }

    const name = ps.slice(2).join(" ")
    const req = { cmd: "AddServer", ip, port, name }
    try {
        const response = await utils.query(req)
        const msg = response.msg
        utils.log(`server: ${msg}`)
        refreshUI()
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

const kad_bootstrap_param = ref("")
async function bootstrapKAD() {
    const p = kad_bootstrap_param.value || ""
    const req = {}
    if (utils.isIPv4Addr(p)) {
        req["cmd"] = "KADConnectIPPort"
        const { ipv4, port } = utils.parseIPv4Addr(p)
        if (ipv4 > 0) {
            req["ipv4"] = `${ipv4}`
            req["port"] = `${port}`
            utils.log(`connect KAD IPv4: ${p}`)
        } else {
            window.dialogs.alert(t("servers.invalid_ipv4"))
            return
        }
    } else if (p.startsWith("http")) {
        req["cmd"] = "KADConnectURL"
        req["url"] = p
        utils.log(`connect KAD URL: ${p}`)
    } else {
        window.dialogs.alert(`Invalid bootstrap param: ${p}`)
        return
    }

    try {
        const response = await utils.query(req)
        const msg = response.msg
        utils.log(`server: ${msg}`)
        const addr = utils.extractIPv4Addr(msg)
        if (addr && addr.length) {
            utils.log(`parse ipv4: ${addr}`)
        }
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function connectKAD() {
    utils.log(`start KAD from known node`)
    try {
        const response = await utils.query({ cmd: "StartKAD" })
        const msg = response.msg
        utils.log(`server: ${msg}`)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function disconnectKAD() {
    try {
        const response = await utils.query({ cmd: "DisconnectKAD" })
        utils.log(`server: ${response.msg}`)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

const connectActions = [
    { label: t(`servers.disconnect_server`), action: "server" },
    { label: t(`servers.disconnect_kad`), action: "dis_kad" },
    { label: t(`servers.connect_kad`), action: "conn_kad" },
]

function handleConnectAction(action) {
    if (action === "server") {
        disconnectServer()
    } else if (action === "dis_kad") {
        disconnectKAD()
    } else if (action === "conn_kad") {
        connectKAD()
    } else {
        window.dialogs.alert(`unknow disconnect target: ${action}`)
    }
}

let pullCtrl
onMounted(async function () {
    curServ.value = store.servAddr
    await refreshUI()

    pullCtrl = compos.pullHash("GetServerAddressHash", (cur, prev) => {
        utils.log(`refresh: UI, prev hash: ${prev}, cur hash: ${cur}`)
        refreshUI()
    })
})

onUnmounted(function () {
    pullCtrl?.stop()
})
</script>

<template>
    <div class="toolbar mw1080-left">
        <div class="toolstrip">
            <i class="fa fa-sort-alpha-asc mw1080-hide" aria-hidden="true"></i>
            <select v-model="sortTag" class="mw1080-hide">
                <option value="name">{{ t("servers.name") }}</option>
                <option value="users">{{ t("servers.users") }}</option>
                <option value="files">{{ t("servers.files") }}</option>
            </select>
            <select v-model="sortOrder" class="mw1080-hide" style="margin-right: 1rem">
                <option value="ascending">{{ t("app.ascending") }}</option>
                <option value="descending">{{ t("app.descending") }}</option>
            </select>

            <DropdownButton
                style="margin-right: 1rem"
                :button-label="t('servers.connect')"
                :menu-items="connectActions"
                @action="handleConnectAction"
            />

            <input v-model="kad_bootstrap_param" class="kad-param" placeholder="URL, IPv4:port" />
            <button @click="bootstrapKAD()" style="margin-right: 1rem">
                {{ t("servers.bootstrap_kad") }}
            </button>

            <input v-model="new_server_param" class="kad-param" placeholder="IPv4:port serv_name" />
            <button @click="addNewServer()" style="margin-right: 1rem">
                {{ t("servers.add_new_server") }}
            </button>
        </div>
    </div>
    <div class="table-header mw1080-left">
        <span style="width: 6rem">{{ t("servers.action") }}</span>
        <span class="server-name-col" style="cursor: pointer" @click="switchSortKeyTo('name')"
            >{{ t("servers.name") }}{{ getOrderSign("name") }}</span
        >
        <span style="width: 6rem; cursor: pointer" @click="switchSortKeyTo('files')"
            >{{ t("servers.files") }}{{ getOrderSign("files") }}</span
        >
        <span style="width: 6rem; cursor: pointer" @click="switchSortKeyTo('users')"
            >{{ t("servers.users") }}{{ getOrderSign("users") }}</span
        >
        <span style="width: 10rem">{{ t("servers.address") }}</span>
        <span class="server-desc-col" style="justify-content: start; padding-left: 1rem">{{
            t("servers.description")
        }}</span>
    </div>
    <div class="container">
        <div
            class="table-row"
            v-for="(item, index) in sortedServers"
            :key="index"
            :style="{ 'font-weight': isConnected(item.address) ? 'bold' : 'unset' }"
        >
            <span style="width: 6rem; flex-shrink: 0">
                <span v-if="isConnected(item.address)">
                    {{ t("servers.active") }}
                </span>
                <div v-else>
                    <button
                        @click="connectServer(item.ip, item.port)"
                        :title="t('servers.connect')"
                    >
                        <i class="fa fa-plug" aria-hidden="true"></i>
                    </button>
                    <button @click="removeServer(item.ip, item.port)" :title="t('servers.remove')">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </span>
            <span class="server-name-col">{{ item.name }}</span>
            <span style="width: 6rem; text-align: right; flex-shrink: 0">{{ item.files }}</span>
            <span style="width: 6rem; text-align: right; flex-shrink: 0">{{ item.users }}</span>
            <span
                style="width: 10rem; text-align: right; flex-shrink: 0; padding-right: 0.5rem"
                :data-ipv4="item.ip"
                :data-port="item.port"
            >
                {{ item.address }}
            </span>
            <span class="server-desc-col" style="text-align: left; padding-left: 1rem">
                {{ item.description }}
            </span>
        </div>

        <div style="width: 100%; height: 3rem"><!-- place holder --></div>
    </div>
</template>

<style scoped>
.server-name-col {
    width: 15rem;
    flex-shrink: 0;
}

.server-desc-col {
    flex-grow: 1;
}

.kad-param {
    width: 10rem;
}

.container {
    display: flex;
    flex-direction: column;
}

.table-row {
    display: flex;
    font-size: 0.9rem;
    width: 100%;
    text-align: center; /* 内容居中对齐 */
    word-break: break-all;
}

/* --- 偶数行背景加深 --- */
.table-row:nth-child(even) {
    background-color: var(--table-row-even);
}

.table-row span {
    padding: 0.125rem;
}

@media (max-width: 1080px) {
    .server-name-col {
        flex-grow: 1;
        flex-shrink: 1;
    }

    .server-desc-col {
        display: none;
    }
}
</style>
