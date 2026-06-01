import { ref, watch } from "vue"
import utils from "@/libs/utils.js"

export default {
    useLocalStorage,
    startPulling,
    pullHash,
}

function useLocalStorage(key, defaultValue) {
    // 1. 初始化：尝试从 localStorage 读取数据，如果没有则使用默认值
    const storedValue = ref(JSON.parse(localStorage.getItem(key)) || defaultValue)

    // 2. 监听变化：当响应式数据改变时，自动同步到 localStorage
    // deep: true 确保能监听到对象或数组内部深层属性的变化
    watch(
        storedValue,
        (newVal) => {
            localStorage.setItem(key, JSON.stringify(newVal))
        },
        { deep: true },
    )

    return storedValue
}

// response must has data.hash
function pullHash(cmd, onchange) {
    let prev = 0
    function onPull(resp) {
        try {
            const newHash = resp.data.hash
            if (prev !== newHash) {
                onchange(newHash, prev)
                prev = newHash
            }
        } catch {}
    }
    return startPulling({ cmd }, onPull)
}

// 封装通用的轮询 Hook
function startPulling(request, resolve, reject, interval = 5000) {
    let handle
    let stopped = false

    async function doWork() {
        try {
            const resp = await utils.query(request)
            resolve(resp)
        } catch (err) {
            reject && reject(err)
        } finally {
            if (!stopped) {
                handle = setTimeout(doWork, interval)
            }
        }
    }

    function stop() {
        stopped = true
        // utils.log(`[debug] call stop: ${utils.formatJson(request)}`)
        clearTimeout(handle)
    }

    function restart() {
        stopped = false
        handle = setTimeout(doWork, interval)
    }

    // unmount would not work!!
    // onUnmounted(() => {
    //     clearTimeout(handle)
    // })

    restart()

    return {
        stop,
        restart,
    }
}
