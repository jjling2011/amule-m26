<script setup>
const githubUrl = "https://github.com/jjling2011/amule-m26/"
const dockerUrl = "https://github.com/jjling2011/amule-m26/pkgs/container/amule-m26"
import { useI18n } from "vue-i18n"

const { t } = useI18n()

function goto(url) {
    window.open(url, "_blank")
}
</script>

<template>
    <div class="toolbar">
        <div class="toolstrip">
            <button @click="goto(githubUrl)" :title="githubUrl">GitHub</button>
            <button @click="goto(dockerUrl)" :title="dockerUrl">Docker</button>
        </div>
    </div>
    <div class="table-header">
        <!-- pass -->
    </div>
    <div class="container">
        <pre class="about-content">{{ t("about.note") }}</pre>
    </div>
</template>

<style scoped>
.container {
    display: flex;
}

.toolstrip button {
    margin-right: 0.5rem;
}

.about-content {
    padding: 1rem;
    font-size: 0.9rem;
}

@media (max-width: 800px) {
    .toolbar,
    .table-header {
        left: 5rem;
    }
}
</style>
