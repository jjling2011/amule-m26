import { ref, watch } from "vue"
import utils from "@/libs/utils.js"

export default {
    useLocalStorage,
    startPulling,
    pullHash,

    debounceRef,
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

/**
 * 防抖辅助函数
 * @param {Function} func - 需要进行防抖处理的目标函数
 * @param {number} delay - 延迟时间（毫秒），默认 300ms
 * @returns {Function} - 返回一个新的防抖化后的函数
 */
function debounce(func, delay = 300) {
    let timer = null

    // 返回的这个函数就是用户实际调用的高频事件处理函数
    return function (...args) {
        // 保存当前上下文，确保原函数的 this 指向正确（例如在 DOM 事件中指向触发元素）
        const context = this

        // 如果在延迟时间内再次触发，清除上一次的定时器，重新开始计时
        if (timer) {
            clearTimeout(timer)
        }

        // 设置新的定时器
        timer = setTimeout(() => {
            // 延迟时间到了，执行目标函数，并传入正确的 this 和参数
            func.apply(context, args)
        }, delay)
    }
}

function debounceRef(rawRef, debouncedRef) {
    const updater = debounce(function (kw) {
        debouncedRef.value = kw
    })
    watch(rawRef, (newValue) => {
        updater(newValue)
    })
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

    function trigger() {
        clearTimeout(handle)
        doWork()
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
        trigger,
        stop,
        restart,
    }
}
