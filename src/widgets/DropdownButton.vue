<script setup>
import { ref, onMounted, onUnmounted } from "vue"

// 定义从父组件接收的 props
const props = defineProps({
    // 按钮显示的文本
    buttonLabel: {
        type: String,
        default: "Action",
    },
    // 菜单项数据数组
    menuItems: {
        type: Array,
        required: true,
        default: () => [],
    },
})

// 定义向父组件发射的事件
const emit = defineEmits(["action"])

const isOpen = ref(false)
const dropdownRef = ref(null)

const toggleMenu = () => {
    isOpen.value = !isOpen.value
}

const handleItemClick = (item) => {
    emit("action", item.action) // 将点击的操作类型传给父组件
    isOpen.value = false
}

// 点击页面其他区域时关闭菜单
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false
    }
}

onMounted(() => document.addEventListener("click", handleClickOutside))
onUnmounted(() => document.removeEventListener("click", handleClickOutside))
</script>

<template>
    <div class="dropdown-container" ref="dropdownRef">
        <!-- 触发按钮：文本从 props 获取 -->
        <button class="dropdown-trigger" @click.stop="toggleMenu">
            {{ buttonLabel }} <span class="arrow" :class="{ 'arrow-up': isOpen }">▼</span>
        </button>

        <!-- 下拉菜单列表：数据源从 props 获取 -->
        <Transition name="fade">
            <ul v-if="isOpen" class="dropdown-menu">
                <li
                    v-for="(item, index) in menuItems"
                    :key="index"
                    class="dropdown-item"
                    @click.stop="handleItemClick(item)"
                >
                    {{ item.label }}
                </li>
            </ul>
        </Transition>
    </div>
</template>

<style scoped>
/* 样式保持与之前一致，此处省略以节省篇幅 */
.dropdown-container {
    position: relative;
    display: inline-block;
}

.dropdown-trigger {
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.arrow {
    font-size: 0.8rem;
    color: var(--fg-color);
    transition: transform 0.3s;
    margin: 0;
    margin-left: 0.5rem;
}

.arrow-up {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    z-index: 500;
    min-width: 100%;
    padding: 0.5rem;
    margin: 0;
    list-style: none;
    color: black;
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    white-space: nowrap;
}

.dropdown-item {
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    font-size: 0.9rem;
    color: black;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: lightgray;
}

.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
