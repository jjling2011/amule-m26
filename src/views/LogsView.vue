<script setup>
import { onMounted, onUnmounted, ref, computed, nextTick } from "vue"
import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"
import efixer from "@/libs/encoding-fixer.js"
import compos from "@/libs/compos.js"

const { t } = useI18n()

const logType = compos.useLocalStorage("m26-logs-cat", "amule")
const allLogs = ref({})
const textareaRef = ref(null)
const autoScroll = ref(true)

let isAutoScroll = false
const curLogText = computed(() => {
    const cat = getCurCat()
    if (autoScroll.value && cat !== "stats") {
        nextTick(() => {
            if (textareaRef.value) {
                isAutoScroll = true
                textareaRef.value.scrollTop = textareaRef.value.scrollHeight
            }
        })
    }
    const log = allLogs.value[cat]
    if (cat !== "amule") {
        return log
    }
    const lines = (log || "").split(/[\r\n]/).map((s) => efixer.autoFixString(s))
    return lines.join("\n")
})

function onTextareaScroll() {
    const cat = getCurCat()
    if (!isAutoScroll && cat !== "stats") {
        autoScroll.value = false
    }
    isAutoScroll = false
}

function getCurCat() {
    const cats = ["server", "amule", "stats"]
    const cat = logType.value
    if (cats.indexOf(cat) < 0) {
        return cats[0]
    }
    return cat
}

async function clearAllLogs() {
    try {
        const resp = await utils.query({ cmd: "ClearLogs" })
        utils.log(`server: ${resp.msg}`)
        setLog(resp)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

function setLog(resp) {
    allLogs.value = resp.data
}

let pullCtrl
onMounted(async function () {
    pullCtrl = compos.startPulling({ cmd: "GetLogs" }, setLog)
    pullCtrl.trigger()
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
                <option value="stats">stats</option>
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
        <span style="flex-grow: 1">{{ logType }}</span>
    </div>
    <div class="container">
        <textarea
            ref="textareaRef"
            v-model="curLogText"
            @scroll="onTextareaScroll"
            spellcheck="false"
        ></textarea>
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

@media (max-width: 1080px) {
    .toolbar,
    .table-header {
        left: 5rem;
    }
}
</style>
