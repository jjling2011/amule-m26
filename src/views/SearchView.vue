<script setup>
import { onMounted, onUnmounted, ref, computed, watch } from "vue"
import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"
import efixer from "@/libs/encoding-fixer.js"
import compos from "@/libs/compos.js"

const { t } = useI18n()

//#region select
const selectAll = ref(false)
const triggerRef = ref(0)
function onSelectAllChange() {
    const checked = selectAll.value
    for (let d of orderedModel.value) {
        d.checked = checked
    }
    triggerRef.value++
}
//#endregion

//#region computed
const sortTag = compos.useLocalStorage("m26-search-sort-tag", "none")
const sortOrder = compos.useLocalStorage("m26-search-sort-ordering", "descending")
const filterKeyword = ref("")

const rawFilterKeyword = ref("")
const updateFilterKeyword = utils.debounce(function (kw) {
    filterKeyword.value = kw
})
watch(rawFilterKeyword, (newValue) => {
    updateFilterKeyword(newValue)
})

function getOrderSign(name) {
    if (name !== sortTag.value) {
        return ""
    }
    return sortOrder.value === "descending" ? " ↓" : " ↑"
}

function transform(file, selected, name_hr, dlHashes) {
    const r = utils.clone(file)
    r["name_hr"] = name_hr
    r["size_hr"] = utils.formatBytes(file.size)
    r["downloading"] = dlHashes.indexOf(file.hash) >= 0
    r["checked"] = !r.downloading && selected.indexOf(file.hash) >= 0
    return r
}

const orderedModel = computed((prev) => {
    const trigger_recompute = triggerRef.value

    const cur_selected = (prev || []).filter((d) => d.checked).map((d) => d.hash)
    const files = dataModel.value?.search || []
    const taskHashes = dataModel.value?.hashes || []

    const r = []
    const filters = utils.buildFilters(filterKeyword.value)
    for (let file of files) {
        const name_hr = efixer.autoFixString(file.name)
        if (!filters.textf(name_hr)) {
            const t = transform(file, cur_selected, name_hr, taskHashes)
            if (!filters.sizef(t.size) && !filters.condf(t.checked)) {
                r.push(t)
            }
        }
    }

    const sortKey = sortTag.value || "none"
    if (sortKey !== "none") {
        const strKeys = ["name_hr"]
        const isNumKey = strKeys.indexOf(sortKey) < 0
        utils.sortInPlace(r, sortKey, isNumKey, sortOrder.value === "descending")
    }
    setTimeout(() => countSelected(), 500)
    return r
})

function switchSortKeyTo(key) {
    if (sortTag.value === key) {
        sortOrder.value = sortOrder.value === "descending" ? "ascending" : "descending"
    } else {
        sortTag.value = key
    }
}

//#endregion

const selectedModelCount = ref(0)
function countSelected() {
    selectedModelCount.value = (orderedModel.value || []).filter((d) => d.checked).length
}

function countTotal() {
    return dataModel.value?.search?.length || 0
}

const dataModel = ref(emptyDataModel())
function emptyDataModel() {
    return {
        search: [],
        download: [],
    }
}

async function refreshUI() {
    try {
        const rSearch = await utils.query({ cmd: "GetSearchResult" })
        const rTaskHashes = await utils.query({ cmd: "GetAllTaskHashes" })
        const hashes = Object.keys(rTaskHashes.data || {})
        const us = utils.uniqueByKey(rSearch.data || [], "hash")
        dataModel.value = {
            search: us,
            hashes,
        }
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

const searchKeyword = ref("")
const searchNetwork = compos.useLocalStorage("m26-search-network", "global")
async function doSearch() {
    const kw = searchKeyword.value
    utils.log(`search: ${kw}`)
    filterKeyword.value = ""
    try {
        const resp = await utils.query({
            cmd: "DoSearch",
            keyword: kw,
            stype: searchNetwork.value,
        })
        dataModel.value = emptyDataModel()
        utils.log(`server: ${resp.msg}`)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

const selectedCat = compos.useLocalStorage("m26-search-selected-cat", "0")
const cats = ref([])
async function updateCats() {
    try {
        const response = await utils.query({ cmd: "GetCats" })
        cats.value = response.data
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function downloadOneFile(hash) {
    try {
        const cat = selectedCat.value || "0"
        const req = { cmd: "DoDownloadFiles", [hash]: cat }
        const resp = await utils.query(req)
        utils.log(`server:`, resp.msg)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
    refreshUI()
}

async function downloadSelectedFiles() {
    const files = (orderedModel.value || []).filter((d) => d.checked)
    if (files.length < 1) {
        window.dialogs.alert(t("search.please_select_files"))
        return
    }

    try {
        const cat = selectedCat.value || "0"
        const req = { cmd: "DoDownloadFiles" }
        for (let file of files) {
            req[file.hash] = cat
            // file.checked = false
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
    updateCats()

    pullCtrl = compos.pullHash("GetSearchHash", (cur, prev) => {
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
            <i class="fa fa-sort-alpha-asc hide-w1080" aria-hidden="true"></i>
            <select v-model="sortTag" class="select-sort-tag hide-w1080">
                <option value="size">{{ t("search.size") }}</option>
                <option value="sources">{{ t("search.sources") }}</option>
                <option value="name_hr">{{ t("search.name") }}</option>
                <option value="none">{{ t("search.none") }}</option>
            </select>
            <select
                v-model="sortOrder"
                class="select-sort-direction hide-w1080"
                style="margin-right: 1rem"
            >
                <option value="ascending">{{ t("app.ascending") }}</option>
                <option value="descending">{{ t("app.descending") }}</option>
            </select>

            <select class="search-param" v-model="searchNetwork">
                <option value="local">Local</option>
                <option value="global">Global</option>
                <option value="kad">KAD</option>
            </select>
            <input
                id="search-input"
                v-model="searchKeyword"
                @keydown.enter="doSearch()"
                :placeholder="t('nav.search') + '<Enter>'"
                style="width: 7rem"
            />
            <button @click="doSearch()" style="margin-right: 1rem">{{ t("nav.search") }}</button>

            <button @click="downloadSelectedFiles">
                {{ t("search.download_to") }}
            </button>
            <select v-model="selectedCat" class="select-category" style="margin-right: 1rem">
                <option v-for="(item, index) in cats" :value="index">{{ item }}</option>
            </select>

            <input
                id="filter-input"
                v-model="rawFilterKeyword"
                :placeholder="t('app.filter')"
                style="width: 12rem"
            />
        </div>
    </div>
    <div class="table-header">
        <span style="width: 3rem">
            <input type="checkbox" v-model="selectAll" @change="onSelectAllChange" />
        </span>
        <span
            style="width: 4rem; flex-shrink: 0; text-align: right; cursor: pointer"
            @click="switchSortKeyTo('sources')"
            >{{ t("search.sources") }}{{ getOrderSign("sources") }}</span
        >
        <span style="width: 5rem; flex-shrink: 0; cursor: pointer" @click="switchSortKeyTo('size')"
            >{{ t("search.size") }}{{ getOrderSign("size") }}</span
        >
        <span style="flex-grow: 1; cursor: pointer" @click="switchSortKeyTo('name_hr')"
            >{{ t("search.name") }}{{ getOrderSign("name_hr") }} ({{ selectedModelCount }} /
            {{ countTotal() }})</span
        >
    </div>
    <div class="container">
        <div
            v-for="(item, index) in orderedModel"
            :key="index"
            class="table-row"
            :style="{ 'font-weight': item.checked ? 'bold' : 'unset' }"
        >
            <label class="table-label" style="cursor: pointer">
                <input
                    type="checkbox"
                    v-model="item.checked"
                    :value="item.hash"
                    @change="countSelected"
                    :disabled="item.downloading"
                />

                <span style="width: 4rem; text-align: center; flex-shrink: 0">{{
                    item.sources
                }}</span>
                <span style="width: 5rem; text-align: right; flex-shrink: 0">{{
                    item.size_hr
                }}</span>
                <button
                    @click="downloadOneFile(item.hash)"
                    :disabled="item.downloading"
                    style="margin: 0.25rem 0.5rem 0.25rem 0.5rem"
                >
                    <i class="fa fa-download" aria-hidden="true" :title="t('nav.download')"></i>
                </button>
                <span
                    style="flex-grow: 1; text-align: left"
                    :data-raw-name="item.name"
                    :class="{ 'disabled-font-color': item.downloading }"
                >
                    {{ item.name_hr }}</span
                >
            </label>
        </div>
        <div style="width: 100%; height: 3rem"><!-- place holder --></div>
    </div>
</template>

<style scoped>
.disabled-font-color {
    color: gray;
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

.table-label {
    display: flex;
    flex-grow: 1;
    justify-content: left;
    align-items: center;
    padding-right: 1rem;
}

.table-label input:nth-child(1) {
    margin-left: 1.2rem;
    margin-right: 1rem;
}

.table-label button:nth-child(1) {
    flex-shrink: 0;
    margin: 0.125rem 0.5rem;
    margin-left: 1rem;
}

@media (max-width: 1080px) {
    .hide-w1080 {
        display: none;
    }

    .toolbar,
    .table-header {
        left: 5rem;
    }
}
</style>
