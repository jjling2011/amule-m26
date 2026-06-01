<script setup>
import { onMounted, onUnmounted, ref, computed, nextTick } from "vue"
import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"
import compos from "@/libs/compos.js"

const { t } = useI18n()

const logType = compos.useLocalStorage("m26-logs-cat", "amule")
const allLogs = ref({})
const textareaRef = ref(null)
const autoScroll = ref(true)

const curLogText = computed(() => {
    const cat = getCurCat()
    if (autoScroll.value) {
        nextTick(() => {
            if (textareaRef.value) {
                textareaRef.value.scrollTop = textareaRef.value.scrollHeight
            }
        })
    }
    return allLogs.value[cat]
})

function getCurCat() {
    return logType.value === "server" ? "server" : "amule"
}

async function refreshUI() {
    try {
        const resp = await utils.query({ cmd: "GetLogs" })
        setLog(resp)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function clearAllLogs() {
    try {
        const resp = await utils.query({ cmd: "ClearLogs" })
        utils.log(`server: ${resp.msg}`)
        refreshUI()
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

function setLog(resp) {
    allLogs.value = resp.data
}

let pullCtrl
onMounted(async function () {
    const req = { cmd: "GetLogs" }

    await refreshUI()
    pullCtrl = compos.startPulling(req, setLog)
})

onUnmounted(function () {
    pullCtrl?.stop()
})
</script>

<template>
    <div class="toolbar">
        <div class="toolstrip">
            <span>{{ t("logs.cat") }}</span>
            <select v-model="logType" style="margin-right: 1rem">
                <option value="amule">amule</option>
                <option value="server">server</option>
            </select>
            <button @click="clearAllLogs()" style="margin-right: 1rem">
                {{ t("logs.clear_all_logs") }}
            </button>
            <label style="display: flex">
                <input type="checkbox" v-model="autoScroll" style="width: 1rem" />
                <span>{{ t("logs.autoscroll") }}</span>
            </label>
        </div>
    </div>
    <div class="table-header">
        <span style="flex-grow: 1">Logs</span>
    </div>
    <div class="container">
        <textarea ref="textareaRef" v-model="curLogText"></textarea>
    </div>
</template>

<style scoped>
.container {
    display: flex;
    width: 97%;
    margin: auto;
    height: 91%;
}

.container textarea {
    height: 100%;
    border-radius: 0.5rem;
    background-color: var(--bg-color);
    width: 100%;
    margin-top: 1rem;
    padding: 0.5rem 1rem;
}

@media (max-width: 800px) {
    .toolbar,
    .table-header {
        left: 5rem;
    }
}
</style>
