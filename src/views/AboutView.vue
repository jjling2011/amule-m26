<script setup>
import { useI18n } from "vue-i18n"
const { t } = useI18n()

function logout() {
    window.location.href = "login.php"
}
</script>

<template>
    <div class="toolbar">
        <div class="toolstrip">
            <button @click="logout">
                <i class="fa fa-sign-out" aria-hidden="true" style="color: var(--fg-color)"></i>
                {{ t("about.logout") }}
            </button>
        </div>
    </div>
    <div class="table-header">
        <!-- pass -->
    </div>
    <div class="container">
        <pre class="about-content">
Tips:
Click app logo to scroll to top.

GitHub:
https://github.com/jjling2011/amule-m26/

Credits:
https://github.com/ngosang/docker-amule/
https://github.com/MatteoRagni/AmuleWebUI-Reloaded/
https://github.com/amule-project/

2026-06
        </pre>
    </div>
</template>

<style scoped>
.container {
    display: flex;
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
