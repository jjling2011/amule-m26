<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue"
import { useI18n } from "vue-i18n"
import utils from "@/libs/utils.js"

const { t, locale } = useI18n({ useScope: "global" })

// 绑定当前的语言状态
const currentLocale = ref(utils.getCurLangName())
watch(currentLocale, (newLocale) => {
    utils.switchLanguage(locale, newLocale)
    window.dialogs.updateText(t("dialogs.ok"), t("dialogs.cancel"))
})

// 绑定当前的主题状态
const currentTheme = ref(utils.getCurThemeMode())
watch(currentTheme, (newTheme) => {
    utils.switchTheme(newTheme)
})

function isDisabled(cat, key) {
    if (!ctrl_disalbed[cat]) {
        return false
    }
    if (key && ctrl_disalbed[cat][key]) {
        return true
    }
    return false
}

const ctrl_disalbed = {
    connection: {
        max_line_up_cap: true,
        max_line_down_cap: true,
    },
    files: {
        preview_prio: true,

        upload_full_chunks: true,
        first_last_chunks_prio: true,
        start_next_paused: true,
        resume_same_cat: true,
        save_sources: true,
    },
    webserver: {
        autorefresh_time: true,
    },
    coretweaks: {
        max_conn_5sec: true,
    },
}

function isCheckbox(cat, key) {
    if (!ctrl_checkbox[cat]) {
        return false
    }
    if (key && ctrl_checkbox[cat][key]) {
        return true
    }
    return false
}

const ctrl_checkbox = {
    connection: {
        udp_dis: true,
        autoconn_en: true,
        reconn_en: true,
        network_ed2k: true,
        network_kad: true,
    },
    files: {
        ich_en: true,
        aich_trust: true,
        new_files_paused: true,
        new_files_auto_dl_prio: true,
        preview_prio: 0,
        new_files_auto_ul_prio: true,
        upload_full_chunks: 0,
        first_last_chunks_prio: 0,
        start_next_paused: 0,
        resume_same_cat: 0,
        save_sources: 0,
        extract_metadata: true,
        alloc_full: true,
        check_free_space: true,
    },
    webserver: {
        use_gzip: true,
    },
    coretweaks: {
        max_conn_5sec: 20,
    },
}

const prefs = ref({})

async function refreshUI() {
    try {
        const response = await utils.query({ cmd: "GetPrefs" })
        prefs.value = response.data
        // utils.log(`prefs: ${utils.formatJson(prefs.value)}`)
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

async function applyPrefs() {
    const p = utils.flattenObject(prefs.value)
    // utils.log(utils.formatJson(p))
    p["cmd"] = "ApplyPrefs"
    try {
        const response = await utils.query(p)
        utils.log(`server: ${response.msg}`)
        window.dialogs.alert("Ok!")
        refreshUI()
    } catch (err) {
        window.dialogs.alert(err.message)
    }
}

function getDescription(cat, key) {
    if (isDisabled(cat, key)) {
        return t("prefs.readonly")
    }
    const k = key ? `prefs.dtable.${cat}.${key}` : `prefs.dtable.${cat}`
    return t(k)
}

onMounted(async function () {
    await refreshUI()
})

onUnmounted(function () {
    // pass
})
</script>

<template>
    <div class="toolbar">
        <div class="toolstrip">
            <div class="switcher-group">
                <label for="lang-select">Language</label>
                <select id="lang-select" v-model="currentLocale">
                    <option value="zh">简体中文</option>
                    <option value="en">English</option>
                </select>
            </div>
            <div class="switcher-group">
                <label for="theme-select">{{ t("theme.title") }}</label>
                <select id="theme-select" v-model="currentTheme">
                    <option value="light">{{ t("theme.light") }}</option>
                    <option value="dark">{{ t("theme.dark") }}</option>
                </select>
            </div>

            <button @click="applyPrefs" style="margin-right: 1rem">{{ t("prefs.save") }}</button>
        </div>
    </div>
    <div class="table-header">
        <span style="width: 16rem">{{ t("prefs.key") }}</span>
        <span style="width: 10rem">{{ t("prefs.value") }}</span>
        <span style="flex-grow: 1; justify-content: start">{{ t("prefs.description") }}</span>
    </div>
    <div class="container">
        <div v-for="(pref, cat) in prefs" :key="cat" class="table-row">
            <div class="pref-cat">{{ cat }}</div>
            <div
                v-if="typeof pref === 'object'"
                v-for="(pval, pkey) in pref"
                :key="pkey"
                class="pref-row"
            >
                <span class="pref-key"> {{ pkey }}</span>
                <div class="pref-value">
                    <span v-if="isDisabled(cat, pkey)" class="pref-span">{{ pval }}</span>
                    <input
                        v-else-if="isCheckbox(cat, pkey)"
                        v-model="prefs[cat][pkey]"
                        type="checkbox"
                        :id="pkey"
                        :true-value="1"
                        :false-value="0"
                        class="prefs-checkbox"
                    />
                    <input v-else v-model="prefs[cat][pkey]" class="prefs-input" />
                </div>
                <span style="flex-grow: 1; padding-left: 0.5rem">{{
                    getDescription(cat, pkey)
                }}</span>
            </div>
            <div v-else class="pref-row">
                <span class="pref-key"> {{ cat }}</span>
                <div class="pref-value">
                    <span v-if="isDisabled(cat)" class="pref-span">{{ pref }}</span>
                    <input
                        v-else-if="isCheckbox(cat)"
                        v-model="prefs[cat]"
                        type="checkbox"
                        :id="cat"
                        :true-value="1"
                        :false-value="0"
                        class="prefs-checkbox"
                    />
                    <input v-else v-model="prefs[cat]" class="prefs-input" />
                </div>
                <span style="flex-grow: 1; padding-left: 0.5rem">{{ getDescription(cat) }}</span>
            </div>
        </div>
        <div style="width: 100%; height: 3rem"><!-- place holder --></div>
    </div>
</template>

<style scoped>
.switcher-group {
    display: block;
    margin-right: 1rem;
}

.switcher-group label {
    display: inline-block;
    /* width: 5rem; */
    color: var(--sidebar-fg-color);
    margin-right: 0.5rem;
    text-align: right;
}

.switcher-group select {
    display: inline-block;
    width: 7rem;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
}

.switcher-group select,
.switcher-group select option {
    color: black;
    background-color: whitesmoke;
}

.container {
    display: flex;
    flex-direction: column;
}

.table-row {
    flex-grow: 1;
}

.pref-cat {
    flex-grow: 1;
    padding: 0.125rem 1rem;
    background-color: var(--prefs-cat);
    display: flex;
    font-size: 1.1rem;
}

.pref-row {
    flex-grow: 1;
    display: flex;
    flex-direction: row;
    background-color: var(--prefs-row);
    align-items: center;
}

.pref-row:nth-child(even) {
    background-color: var(--bg-color);
}

.pref-key {
    width: 12rem;
    text-align: left;
    padding: 0.125rem 1rem 0.125rem 3rem;
}

.pref-value {
    width: 9rem;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0.125rem 0.5rem;
}

.pref-span {
    font-size: 1rem;
}

.prefs-checkbox {
    width: 1rem;
}

.prefs-input {
    text-align: center;
    width: 8rem;
    padding: 0.25rem 0.5rem;
}

@media (max-width: 800px) {
    .toolbar,
    .table-header {
        left: 5rem;
    }
}
</style>
