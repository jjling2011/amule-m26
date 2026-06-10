<script setup>
import { ref, onMounted, onUnmounted } from "vue"
import utils from "@/libs/utils.js"
import SideBar from "@/views/SideBarView.vue"

// 1. Create a ref to target the specific scrollable element
const scrollableContainer = ref(null)
function scrollMainContainer(toTop) {
    const el = scrollableContainer.value
    if (el) {
        el.scrollTop = toTop ? 0 : el.scrollHeight
    }
}

function scrollUpDown(isDown) {
    const el = scrollableContainer.value
    if (!el) {
        return
    }
    if (isDown) {
        el.scrollTop += el.clientHeight
    } else {
        el.scrollTop -= el.clientHeight
    }
}

// 2. Function to handle the scroll reset
function handleScrollKeydown(event) {
    // conflict with browser shortcuts
    const keys = ["Home", "End", "PageUp", "PageDown"]
    const key = event.key
    if (!event.ctrlKey || keys.indexOf(key) < 0) {
        return
    }
    event.preventDefault()
    switch (key) {
        case "Home":
            scrollMainContainer(true)
            return
        case "End":
            scrollMainContainer(false)
            return
        case "PageUp":
            scrollUpDown(false)
            return
        case "PageDown":
            scrollUpDown(true)
            return
    }
}

// 3. Attach event listener on mount
onMounted(() => {
    // conflict with browser shortcuts
    // window.addEventListener("keydown", handleScrollKeydown)
})

// 4. Clean up listener on unmount to prevent memory leaks
onUnmounted(() => {
    // conflict with browser shortcuts
    // window.removeEventListener("keydown", handleScrollKeydown)
})
</script>

<template>
    <div class="app-container">
        <SideBar @on-app-name-click="scrollMainContainer(true)" />
        <div ref="scrollableContainer" class="main-container">
            <RouterView />
        </div>
    </div>
    <div></div>
</template>

<style scoped>
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

.main-container {
    position: fixed;
    background-color: var(--bg-color);
    /* border-top-left-radius: 1rem; */
    z-index: 1;
    right: 0;
    top: 5rem;
    bottom: 0;
    /* padding: 1rem; */
    left: 14rem;
    overflow-y: auto;
}

@media (max-width: 1080px) {
    .main-container {
        left: 5rem;
    }
}
</style>
