<script setup>
import { onMounted, onUnmounted, ref, computed } from "vue"
import DropdownButton from "@/widgets/DropdownButton.vue"

import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"
import efixer from "@/libs/encoding-fixer.js"
import compos from "@/libs/compos.js"

const { t } = useI18n()

const taskCommands = ["pause", "resume", "cancel", "priodown", "prioup"]
// 在父组件中维护数据和文案
const taskActions = taskCommands.map((cmd) => {
    return { label: t(`download.${cmd}`), action: cmd }
})
taskActions.push({ label: t("download.copy_link"), action: "copy_link" })

const handleTaskAction = (actionType) => {
    if (taskCommands.indexOf(actionType) >= 0) {
        doTaskCommand(actionType)
    } else if (actionType === "copy_link") {
        copy_ed2k_links()
    } else {
        window.dialogs.alert(`unknow ation type: ${actionType}`)
    }
}

const selectAll = ref(false)
const triggerRef = ref(0)
function onSelectAllChange() {
    const checked = selectAll.value
    for (let d of sortedTasks.value) {
        d.checked = checked
    }
    triggerRef.value++
}

const comparers = {
    name: (c) => (a, b) => c(utils.compareString(a.name_hr, b.name_hr)),
    cat: (c) => (a, b) => c(utils.compareString(a.cat_hr, b.cat_hr)),
    size: (c) => (a, b) => c(a.size - b.size),
    speed: (c) => (a, b) => c(a.speed - b.speed),
    prio: (c) => (a, b) => c(utils.compareString(a.prio, b.prio)),
    status: (c) => (a, b) => c(utils.compareString(a.status, b.status)),
    complete: (c) => (a, b) => c(a.complete - b.complete),
    selected: (c) => (a, b) => c(a.checked - b.checked),
}

const sortTag = compos.useLocalStorage("m26-download-sort-tag", "size")
const sortOrder = compos.useLocalStorage("m26-download-sort-order", "descending")

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

const filterSubStr = ref("")

const sortedTasks = computed((prev) => {
    const trigger_recompute = triggerRef.value

    const cur_selected = (prev || []).filter((d) => d.checked).map((d) => d.hash)
    const files = tasks.value.downloads || []
    const cats = tasks.value.cats || []

    const reverse = sortOrder.value === "descending" ? (cond) => -1 * cond : (cond) => cond
    const comparer = utils.getValue(comparers, sortTag.value, "name")(reverse)

    const r = []
    const sub_str = utils.stripSpace(filterSubStr.value)
    for (let file of files) {
        const name_hr = efixer.autoFixString(file.name)
        if (!sub_str || utils.subStrIn(sub_str, name_hr)) {
            r.push(transform(file, cur_selected, name_hr, cats))
        }
    }
    r.sort(comparer)
    setTimeout(() => countSelected(), 500)
    return r
})

function transform(file, selected, name_hr, cats) {
    const r = utils.clone(file)
    r["cat_hr"] = cats[file.cat] || "-"
    r["checked"] = selected.indexOf(file.hash) >= 0
    r["name_hr"] = name_hr
    r["complete"] = (file.size_done / Math.max(1, file.size)) * 100
    r["size_hr"] = `${utils.formatBytes(file.size)}`
    r["complete_hr"] = `${r["complete"].toFixed(1)}%`
    r["speed_hr"] = utils.formatBytes(file.speed)
    return r
}

const tasks = ref({})
async function refreshUI() {
    try {
        const response = await utils.query({ cmd: "GetTasks" })
        const data = response.data
        tasks.value = data
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function copy_ed2k_links() {
    const files = (sortedTasks.value || []).filter((d) => d.checked)
    if (files.length < 1) {
        window.dialogs.alert(t("download.please_select_tasks"))
        return
    }

    const links = files.map((d) => d.link)
    const ok = await utils.copyToClipboard(...links)
    const msg = ok ? t("download.copy_success") : t("download.copy_fail")
    window.dialogs.alert(msg)
}

async function doTaskCommand(taskcmd) {
    if (taskCommands.indexOf(taskcmd) < 0) {
        window.dialogs.alert(`${t("download.unsupported_cmd")} ${taskcmd}`)
        return
    }

    const files = (sortedTasks.value || []).filter((d) => d.checked)
    if (files.length < 1) {
        window.dialogs.alert(t("download.please_select_tasks"))
        return
    }

    if (taskcmd == "cancel") {
        const ok = await window.dialogs.confirm(
            `${t("download.remove")}${files.length}${t("download.tasks")}`,
        )
        if (!ok) {
            return
        }
    }

    try {
        const req = { cmd: "DoTaskCmd" }
        for (let file of files) {
            req[file.hash] = taskcmd
            // file.checked = false
        }
        const resp = await utils.query(req)
        if (resp.ok) {
            utils.log(`server: ${resp.msg}`)
        } else {
            window.dialogs.alert(`${resp.msg}`)
        }
    } catch (err) {
        window.dialogs.alert(err.message)
    }
    refreshUI()
}

const selectedTaskCount = ref(0)
function countSelected() {
    selectedTaskCount.value = (sortedTasks.value || []).filter((d) => d.checked).length
}

function countTotal() {
    return tasks.value?.downloads?.length || 0
}

const selectedCat = ref("0")
async function setFileCatTo() {
    const files = (sortedTasks.value || []).filter((d) => d.checked)
    if (files.length < 1) {
        window.dialogs.alert(t("download.please_select_tasks"))
        return
    }

    try {
        const cat = selectedCat.value || "0"
        const req = { cmd: "SetDownloadFilesCat" }
        for (let file of files) {
            req[file.hash] = cat
        }
        const resp = await utils.query(req)
        utils.log(`server:`, resp.msg)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
    refreshUI()
}

let pullCtrl
onMounted(async function () {
    await refreshUI()
    pullCtrl = compos.pullHash("GetTaskHash", (cur, prev) => {
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
                <option value="selected">{{ t("app.selected") }}</option>
                <option value="name">{{ t("download.name") }}</option>
                <option value="cat">{{ t("download.cat") }}</option>
                <option value="size">{{ t("download.size") }}</option>
                <option value="complete">{{ t("download.complete") }}</option>
                <option value="speed">{{ t("download.speed") }}</option>
                <option value="prio">{{ t("download.prio") }}</option>
                <option value="status">{{ t("download.status") }}</option>
            </select>
            <select v-model="sortOrder" style="margin-right: 1rem">
                <option value="ascending">{{ t("app.ascending") }}</option>
                <option value="descending">{{ t("app.descending") }}</option>
            </select>

            <input
                style="width: 10rem; margin-right: 1rem"
                class="task-subtle-col"
                v-model="filterSubStr"
                :placeholder="t('app.filter') + '(' + t('download.name') + ')'"
            />

            <DropdownButton
                style="margin-right: 1rem"
                :button-label="t('download.task_action')"
                :menu-items="taskActions"
                @action="handleTaskAction"
            />

            <button @click="setFileCatTo">
                {{ t("download.move_to") }}
            </button>
            <select v-model="selectedCat" class="select-category" style="margin-right: 1rem">
                <option v-for="(item, index) in tasks.cats" :value="index">{{ item }}</option>
            </select>
        </div>
    </div>
    <div class="table-header">
        <span style="width: 3rem">
            <input type="checkbox" v-model="selectAll" @change="onSelectAllChange" />
        </span>
        <span style="flex-grow: 1; cursor: pointer" @click="switchSortKeyTo('name')"
            >{{ t("download.name") }}{{ getOrderSign("name") }} ({{ selectedTaskCount }} /
            {{ countTotal() }})</span
        >
        <span style="width: 6rem; cursor: pointer" @click="switchSortKeyTo('cat')"
            >{{ t("download.cat") }}{{ getOrderSign("cat") }}</span
        >
        <span style="width: 5rem; cursor: pointer" @click="switchSortKeyTo('size')"
            >{{ t("download.size") }}{{ getOrderSign("size") }}</span
        >
        <span style="width: 5rem; cursor: pointer" @click="switchSortKeyTo('complete')"
            >{{ t("download.complete") }}{{ getOrderSign("complete") }}</span
        >
        <span class="task-subtle-col" @click="switchSortKeyTo('speed')"
            >{{ t("download.speed") }}{{ getOrderSign("speed") }}</span
        >
        <span class="task-subtle-col" @click="switchSortKeyTo('prio')"
            >{{ t("download.prio") }}{{ getOrderSign("prio") }}</span
        >
        <span class="task-subtle-col" @click="switchSortKeyTo('status')"
            >{{ t("download.status") }}{{ getOrderSign("status") }}</span
        >
    </div>
    <div class="container">
        <div class="table-row" v-for="(item, index) in sortedTasks" :key="index">
            <label class="table-col1" style="cursor: pointer">
                <input
                    type="checkbox"
                    v-model="item.checked"
                    :value="item.hash"
                    @change="countSelected"
                />
                <span
                    :data-link="item.link"
                    :data-raw-name="item.name"
                    style="width: 1rem; flex-grow: 1; justify-content: left; text-align: left"
                >
                    {{ item.name_hr }}
                </span>
            </label>
            <span style="width: 6rem; flex-shrink: 0" :data-cat-index="item.cat">{{
                item.cat_hr
            }}</span>
            <span style="width: 5rem; flex-shrink: 0; justify-content: end">{{
                item.size_hr
            }}</span>
            <span style="width: 5rem; flex-shrink: 0; justify-content: end">{{
                item.complete_hr
            }}</span>
            <span class="task-subtle-col" style="justify-content: end">{{ item.speed_hr }}</span>
            <span class="task-subtle-col">{{ item.prio }}</span>
            <span class="task-subtle-col">{{ item.status }}</span>
        </div>

        <div style="width: 100%; height: 3rem"><!-- place holder --></div>
    </div>
</template>

<style scoped>
.table-row {
    display: flex;
    font-size: 0.9rem;
    width: 100%;
}

.table-row span {
    word-break: break-all;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0.125rem;
}

/* --- 偶数行背景加深 --- */
.table-row:nth-child(even) {
    background-color: var(--table-row-even);
}

.table-col1 {
    display: flex;
    flex-grow: 1;
    justify-content: left;
    align-items: center;
}

.table-col1 input {
    margin-left: 1.2rem;
    margin-right: 0.5rem;
}

.task-subtle-col {
    cursor: pointer;
    flex-shrink: 0;
    width: 6rem;
}

.container {
    display: flex;
    flex-direction: column;
}

@media (max-width: 800px) {
    .toolbar,
    .table-header {
        left: 5rem;
    }

    .task-subtle-col {
        display: none !important;
    }
}
</style>
