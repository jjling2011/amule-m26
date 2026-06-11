<script setup>
import { onMounted, onUnmounted, ref, computed, watch } from "vue"
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

const filterKeyword = ref("")
const rawFilterKeyword = ref("")
compos.debounceRef(rawFilterKeyword, filterKeyword)

const sortedTasks = computed((prev) => {
    const trigger_recompute = triggerRef.value

    const cur_selected = (prev || []).filter((d) => d.checked).map((d) => d.hash)
    const files = tasks.value.downloads || []
    const cats = tasks.value.cats || []

    const r = []
    const filters = utils.buildFilters(filterKeyword.value)
    for (let file of files) {
        const name_hr = efixer.autoFixString(file.name)
        if (!filters.textf(name_hr)) {
            const t = transform(file, cur_selected, name_hr, cats)
            if (
                !filters.condf(t.checked) &&
                !filters.sizef(t.size) &&
                !filters.tagf(t.cat_hr) &&
                !filters.ratiof(t.complete)
            ) {
                r.push(t)
            }
        }
    }

    const sortKey = sortTag.value || "name_hr"
    const strKeys = ["name_hr", "cat_hr", "prio", "status"]
    const isNumKey = strKeys.indexOf(sortKey) < 0
    utils.sortInPlace(r, sortKey, isNumKey, sortOrder.value === "descending")

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
    <div class="toolbar mw1080-left">
        <div class="toolstrip">
            <i class="fa fa-sort-alpha-asc mw1080-hide" aria-hidden="true"></i>
            <select v-model="sortTag" class="mw1080-hide">
                <option value="name_hr">{{ t("download.name") }}</option>
                <option value="cat_hr">{{ t("download.cat") }}</option>
                <option value="size">{{ t("download.size") }}</option>
                <option value="complete">{{ t("download.complete") }}</option>
                <option value="speed">{{ t("download.speed") }}</option>
                <option value="prio">{{ t("download.prio") }}</option>
                <option value="status">{{ t("download.status") }}</option>
            </select>
            <select v-model="sortOrder" style="margin-right: 1rem" class="mw1080-hide">
                <option value="ascending">{{ t("app.ascending") }}</option>
                <option value="descending">{{ t("app.descending") }}</option>
            </select>

            <button @click="setFileCatTo">
                {{ t("download.move_to") }}
            </button>
            <select v-model="selectedCat" class="select-category" style="margin-right: 1rem">
                <option v-for="(item, index) in tasks.cats" :value="index">{{ item }}</option>
            </select>

            <DropdownButton
                style="margin-right: 1rem"
                :button-label="t('download.task_action')"
                :menu-items="taskActions"
                @action="handleTaskAction"
            />

            <input
                style="width: 12rem; margin-right: 1rem"
                v-model="rawFilterKeyword"
                :placeholder="t('app.filter')"
            />
        </div>
    </div>
    <div class="table-header mw1080-left">
        <span style="width: 3rem">
            <input type="checkbox" v-model="selectAll" @change="onSelectAllChange" />
        </span>
        <span style="flex-grow: 1; cursor: pointer" @click="switchSortKeyTo('name_hr')"
            >{{ t("download.name") }}{{ getOrderSign("name_hr") }} ({{ selectedTaskCount }} /
            {{ countTotal() }})</span
        >
        <span style="width: 6rem; cursor: pointer" @click="switchSortKeyTo('cat_hr')"
            >{{ t("download.cat") }}{{ getOrderSign("cat_hr") }}</span
        >
        <span style="width: 5rem; cursor: pointer" @click="switchSortKeyTo('size')"
            >{{ t("download.size") }}{{ getOrderSign("size") }}</span
        >
        <span style="width: 5rem; cursor: pointer" @click="switchSortKeyTo('complete')"
            >{{ t("download.complete") }}{{ getOrderSign("complete") }}</span
        >
        <span class="task-subtle-col mw1080-hide" @click="switchSortKeyTo('speed')"
            >{{ t("download.speed") }}{{ getOrderSign("speed") }}</span
        >
        <span class="task-subtle-col mw1080-hide" @click="switchSortKeyTo('prio')"
            >{{ t("download.prio") }}{{ getOrderSign("prio") }}</span
        >
        <span class="task-subtle-col mw1080-hide" @click="switchSortKeyTo('status')"
            >{{ t("download.status") }}{{ getOrderSign("status") }}</span
        >
    </div>
    <div class="container">
        <div
            class="table-row"
            v-for="(item, index) in sortedTasks"
            :key="index"
            :style="{ 'font-weight': item.checked ? 'bold' : 'unset' }"
        >
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
            <span
                style="width: 5rem; flex-shrink: 0; justify-content: end; padding-right: 0.5rem"
                >{{ item.complete_hr }}</span
            >
            <span class="task-subtle-col mw1080-hide" style="justify-content: end">{{
                item.speed_hr
            }}</span>
            <span class="task-subtle-col mw1080-hide">{{ item.prio }}</span>
            <span class="task-subtle-col mw1080-hide">{{ item.status }}</span>
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
</style>
