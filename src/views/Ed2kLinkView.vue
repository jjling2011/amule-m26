<script setup>
import { onMounted, onUnmounted, ref } from "vue"
import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"
import compos from "@/libs/compos.js"

const { t } = useI18n()

const selectedCat = compos.useLocalStorage("m26-ed2k-selected-cat", "0")
const cats = ref([])
async function updateCats() {
    try {
        const response = await utils.query({ cmd: "GetCats" })
        cats.value = response.data
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

const ed2k = "ed2k://"

const link_text = ref("")

function countLinks() {
    return getLinks().length
}

function getLinks() {
    return (link_text.value || "").split(/[\r\n]/).filter((s) => s && s.startsWith(ed2k))
}

const btnDownloadDisabled = ref(false)

async function downloadEd2kLinks() {
    const links = getLinks()
    if (links.length < 1) {
        window.dialogs.alert(t("ed2k.no_link"))
        return
    }

    const cat = selectedCat.value || "0"
    const req = { cmd: "DoDownloadEd2k", cat }
    for (let i = 0; i < links.length; i++) {
        const prefix = `link${i}`
        req[prefix] = links[i]
    }
    btnDownloadDisabled.value = true
    try {
        const resp = await utils.query(req)
        if (resp.ok) {
            const msg = `${t("ed2k.serv_recv")} ${resp.msg} ${t("ed2k.links_dot")}`
            window.dialogs.alert(msg)
        } else {
            window.dialogs.alert(resp.msg)
        }
    } finally {
        btnDownloadDisabled.value = false
    }
}

onMounted(async function () {
    updateCats()
})

onUnmounted(function () {
    // pass
})
</script>

<template>
    <div class="toolbar mw1080-left">
        <div class="toolstrip">
            <button @click="downloadEd2kLinks" :disabled="btnDownloadDisabled">
                {{ t("search.download_to") }}
            </button>
            <select v-model="selectedCat" class="select-category" style="margin-right: 1rem">
                <option v-for="(item, index) in cats" :value="index">{{ item }}</option>
            </select>
        </div>
    </div>
    <div class="table-header mw1080-left">
        <span style="flex-grow: 1">{{ t("ed2k.links") }} ({{ countLinks() }})</span>
    </div>
    <div class="container">
        <textarea v-model="link_text" placeholder="ed2k://..." spellcheck="false"></textarea>
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
</style>
