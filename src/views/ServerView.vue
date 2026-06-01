<script setup>
import { onMounted, onUnmounted, watch, ref, computed } from "vue"
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

async function disconnectAllServer() {
    try {
        await utils.query({ cmd: "DisconnectServer" })
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function removeServer(event, ip, port) {
    const span = window.$(event.currentTarget)
    const line = span.parent().parent().parent()

    const msg = t("servers.confirmRemoveServer")
    const ok = await window.dialogs.confirm(msg)
    if (!ok) {
        return
    }
    line.hide()
    try {
        utils.log(`try to remove: [${ip}:${port}]`)
        await utils.query({ cmd: "RemoveServer", ip, port })
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

const comparers = {
    name: (c) => (a, b) => c(utils.compareString(a.name, b.name)),
    users: (c) => (a, b) => c(a.users - b.users),
    files: (c) => (a, b) => c(a.files - b.files),
}

const sortTag = compos.useLocalStorage("m26-server-sort-tag", "files")
const sortOrder = compos.useLocalStorage("m26-server-sort-order", "descending")
function switchSortKeyTo(key) {
    if (sortTag.value === key) {
        sortOrder.value = sortOrder.value === "descending" ? "ascending" : "descending"
    } else {
        sortTag.value = key
    }
}

const sortedServers = computed(() => {
    const r = [...servers.value]
    const reverse = sortOrder.value === "descending" ? (cond) => -1 * cond : (cond) => cond
    const comparer = utils.getValue(comparers, sortTag.value, "files")(reverse)
    r.sort(comparer)
    return r
})

const kad_param = ref("")
async function connectKAD() {
    const p = kad_param.value || ""
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
        req["cmd"] = "StartKAD"
        utils.log(`start KAD from known node`)
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

async function disconnectKAD() {
    try {
        const response = await utils.query({ cmd: "DisconnectKAD" })
        utils.log(`server: ${response.msg}`)
    } catch (err) {
        window.dialogs.alert(err.message)
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
    <div class="toolbar">
        <div class="toolstrip">
            <i class="fa fa-sort-alpha-asc" aria-hidden="true"></i>
            <select v-model="sortTag">
                <option value="name">{{ t("servers.name") }}</option>
                <option value="users">{{ t("servers.users") }}</option>
                <option value="files">{{ t("servers.files") }}</option>
            </select>
            <select v-model="sortOrder" style="margin-right: 1rem">
                <option value="ascending">{{ t("app.ascending") }}</option>
                <option value="descending">{{ t("app.descending") }}</option>
            </select>
            <button @click="disconnectAllServer" style="margin-right: 1rem">
                {{ t("servers.disconnect") }}
            </button>
            <span>KAD</span>
            <input v-model="kad_param" class="kad-param" placeholder="URL, IPv4:port" />
            <button @click="connectKAD()">
                {{ t("servers.bootstrap") }}
            </button>
            <button @click="disconnectKAD()" style="margin-right: 1rem">
                {{ t("servers.disconnect") }}
            </button>
        </div>
    </div>
    <div class="table-header">
        <span style="width: 7em">{{ t("servers.action") }}</span>
        <span class="server-name-col" style="cursor: pointer" @click="switchSortKeyTo('name')">{{
            t("servers.name")
        }}</span>
        <span class="server-desc-col">{{ t("servers.description") }}</span>
        <span style="width: 12em">{{ t("servers.address") }}</span>
        <span style="width: 6em; cursor: pointer" @click="switchSortKeyTo('users')">{{
            t("servers.users")
        }}</span>
        <span style="width: 7em; cursor: pointer" @click="switchSortKeyTo('files')">{{
            t("servers.files")
        }}</span>
    </div>
    <div class="container">
        <div class="table-row" v-for="(item, index) in sortedServers" :key="index">
            <span style="width: 7em">
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
                    <button
                        @click="removeServer($event, item.ip, item.port)"
                        :title="t('servers.remove')"
                    >
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </span>
            <span class="server-name-col">{{ item.name }}</span>
            <span class="server-desc-col" style="text-align: left">
                {{ item.description }}
            </span>
            <span
                style="width: 12em; text-align: right"
                :data-ipv4="item.ip"
                :data-port="item.port"
            >
                {{ item.address }}
            </span>
            <span style="width: 6em; text-align: right">{{ item.users }}</span>
            <span style="width: 7em; text-align: right; padding-right: 1rem">{{ item.files }}</span>
        </div>

        <div style="width: 100%; height: 3rem"><!-- place holder --></div>
    </div>
</template>

<style scoped>
.server-name-col {
    width: 15rem;
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

@media (max-width: 800px) {
    .toolbar,
    .table-header {
        left: 5rem;
    }

    .server-name-col {
        flex-grow: 1;
    }

    .server-desc-col {
        display: none;
    }
}
</style>
