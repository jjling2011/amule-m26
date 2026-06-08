<script setup>
const githubUrl = "https://github.com/jjling2011/amule-m26/"
const dockerUrl = "https://github.com/jjling2011/amule-m26/pkgs/container/amule-m26"

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
        <pre class="about-content">
Filter:
hello    include string "hello"
-world   without string "world"
^hello   starts with "hello"
-^world  not starts with "world"
@        selected
>10      size bigger than 10 MiB
&lt;200     size smaller than 200 MiB

Filter keywords are seperated by space.


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
