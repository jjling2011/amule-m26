import { createI18n } from "vue-i18n"

// 引入各个语言的配置文件（你可以按需创建 zh.js, en.js 等）
import langEn from "@/langs/en.js"
import langZh from "@/langs/zh.js"

const langNameStorageKey = "m26-i18n-lang-name"
const themeModeStorageKey = "m26-color-theme-name"

function gotoPage(url) {
    window.location.href = url
}

function reloadPage() {
    window.location.href = window.location.href
}

// to-do: add task queue
function query(data, url, timeout, method) {
    url = url || "./serv.php"
    method = method || (isDevEnv() ? "GET" : "POST")
    timeout = timeout || 12000
    data = data || {}

    function ondata(resolve, reject, response) {
        if (!response || typeof response !== "string" || !response.startsWith("{")) {
            gotoPage("./login.php")
            reject(new Error("not login"))
            return
        }
        try {
            const obj = JSON.parse(response)
            resolve(obj)
        } catch (err) {
            // log(`[debug] query():`, response)
            reject(err)
        }
    }

    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            type: method,
            data: data,
            timeout: timeout,
            success: (response) => ondata(resolve, reject, response),
            error: function (xhr, status, error) {
                if (status === "timeout") {
                    reject(new Error("request timeout"))
                } else {
                    reject(
                        new Error(
                            `request failed!\nerror code: ${status}\nmessage: ${error.message}`,
                        ),
                    )
                }
            },
        })
    })
}

function buildFilters(keyword) {
    const emptyConds = ["-", ">", "<", "#", "-#"]
    const keywords = (keyword || "").split(/ /).filter((s) => s && emptyConds.indexOf(s) < 0)

    const condf = keywords.indexOf("@") < 0 ? () => false : (c) => !c

    const minSize = Math.max(
        ...keywords
            .filter((s) => s.startsWith(">") && !s.endsWith("%"))
            .map((s) => parseInt(s.substring(1)) || -1),
    )

    const maxSize = Math.min(
        ...keywords
            .filter((s) => s.startsWith("<") && !s.endsWith("%"))
            .map((s) => parseInt(s.substring(1)) || Number.MAX_SAFE_INTEGER),
    )

    function sizef(size) {
        size = size / 1024 / 1024
        return size > maxSize || size < minSize
    }

    const minRatio = Math.max(
        ...keywords
            .filter((s) => s.startsWith(">") && s.endsWith("%"))
            .map((s) => parseFloat(s.substring(1)) || -1),
    )

    const maxRatio = Math.min(
        ...keywords
            .filter((s) => s.startsWith("<") && s.endsWith("%"))
            .map((s) => parseFloat(s.substring(1)) || Number.MAX_SAFE_INTEGER),
    )

    function ratiof(percentage) {
        return percentage > maxRatio || percentage < minRatio - 0.001
    }

    const has_cand = keywords
        .filter((s) => {
            const c = s.substring(0, 1)
            const ok = ["-", ">", "<"].indexOf(c) < 0
            return ok
        })
        .map((s) => s.toLowerCase())

    const starts = has_cand
        .filter((s) => s.substring(0, 1) === "^")
        .map((s) => s.substring(1))
        .filter((s) => s)

    const tags = has_cand
        .filter((s) => s.substring(0, 1) === "#")
        .map((s) => s.substring(1))
        .filter((s) => s)
    const has = has_cand.filter((s) => {
        const c = s.substring(0, 1)
        return c !== "^" && c !== "#"
    })

    const not_cand = keywords
        .filter((s) => s.startsWith("-"))
        .map((s) => s.substring(1))
        .map((s) => s.toLowerCase())

    const not_starts = not_cand
        .filter((s) => s.substring(0, 1) === "^")
        .map((s) => s.substring(1))
        .filter((s) => s)

    const not_tags = not_cand
        .filter((s) => s.substring(0, 1) === "#")
        .map((s) => s.substring(1))
        .filter((s) => s)

    const not_has = not_cand.filter((s) => {
        const c = s.substring(0, 1)
        return c !== "^" && c !== "#"
    })

    function tagf(tag) {
        tag = (tag || "").toLowerCase()
        for (let kw of tags) {
            if (!tag.startsWith(kw)) {
                return true
            }
        }
        for (let kw of not_tags) {
            if (tag.startsWith(kw)) {
                return true
            }
        }
        return false
    }

    // log(`has:`, has, `starts:`, starts)
    // log(`not has:`, not_has, `not starts:`, not_starts)

    function textf(s) {
        s = (s || "").toLowerCase()
        for (let kw of starts) {
            if (!s.startsWith(kw)) {
                return true
            }
        }

        for (let kw of not_starts) {
            if (s.startsWith(kw)) {
                return true
            }
        }

        for (let kw of has) {
            const found = s.includes(kw)
            if (!found) {
                return true
            }
        }

        for (let kw of not_has) {
            const found = s.includes(kw)
            if (found) {
                return true
            }
        }
        return false
    }
    // log(`condf(false): ${condf(false)}`)
    // log(`min: ${min}, max: ${max}, sizef(10): ${sizef(10)}`)
    // log(`has: ${formatJson(has)}, hasnot: ${formatJson(hasnot)}, textf("hello"): ${textf("hello")}`)

    return {
        sizef,
        ratiof,
        condf,
        textf,
        tagf,
    }
}

function isIPv4Addr(s) {
    if (!s || typeof s !== "string" || s.startsWith("0.0.0.0:")) {
        return false
    }
    return /^(\d{1,3}\.){3,3}\d{1,3}:\d{1,5}$/.test(s)
}

function parseIPv4Addr(s) {
    const arr = (s || "").split(/[.:]/).map((s) => parseInt(s) || 0)
    if (arr.length < 5) {
        return { ipv4: 0, port: 0 }
    }
    const port = arr[4]
    const ipv4 = arr[0] + (arr[1] << 8) + (arr[2] << 16) + (arr[3] << 24)
    return { ipv4, port }
}

function extractIPv4Addr(s) {
    if (!s || typeof s !== "string") {
        return ""
    }
    const arr = s.split(/[ :]/).map((s) => parseInt(s))
    const len = arr.length
    if (len < 2 || !arr[len - 2]) {
        return ""
    }

    const port = arr[len - 1]
    let ip = arr[len - 2]
    const addr = []
    for (let i = 0; i < 4; i++) {
        addr[i] = `${(ip % 256).toFixed(0)}`
        ip = ip / 256
    }
    return `${addr.join(".")}:${port}`
}

function isDevEnv() {
    return import.meta.env.DEV
}

function getBrowserLang() {
    const browserLang = navigator.language
    if (browserLang === "zh-CN") {
        return "zh"
    }
    return "en"
}

// 获取默认语言的函数
function getCurLangName() {
    const savedLang = localStorage.getItem(langNameStorageKey)
    if (savedLang) {
        return savedLang
    }
    return getBrowserLang()
}

const i18n = createI18n({
    legacy: false, // Vue 3 必须设置为 false，以启用 Composition API 模式
    locale: getCurLangName(), // 默认显示的语言
    fallbackLocale: "en", // 如果找不到对应翻译，回退到英语
    messages: {
        zh: langZh,
        en: langEn,
    },
})

// 切换主题的方法
function switchTheme(theme) {
    if (theme === "auto") {
        theme = isSysThemeModeDark() ? "dark" : "light"
    }
    localStorage.setItem(themeModeStorageKey, theme)
    if (theme === "dark") {
        document.documentElement.setAttribute("data-theme", theme)
    } else {
        document.documentElement.removeAttribute("data-theme")
    }
}

function switchLanguage(locale, lang) {
    if (lang === "auto") {
        lang = getBrowserLang()
    }
    console.log(`switch to: ${lang}`)
    locale.value = lang
    localStorage.setItem(langNameStorageKey, lang)
}

function isSysThemeModeDark() {
    window.matchMedia("(prefers-color-scheme: dark)").matches
}

function getCurThemeMode() {
    const mode = localStorage.getItem(themeModeStorageKey)
    if (mode) {
        return mode
    }
    return isSysThemeModeDark() ? "dark" : "light"
}

function log(...args) {
    console.log(...args)
}

function formatBytes(input) {
    // 1. 边界情况处理：如果无法转换，返回 "-"
    if (!input) {
        return "-"
    }

    // 2. 将字符串转换为数字
    const bytes = Number(input)

    // 3. 校验是否为有效的非负数
    if (isNaN(bytes) || bytes < 0) {
        return "-"
    }

    // 4. 处理 0 字节的特殊情况
    if (bytes === 0) return "0 B"

    // 5. 定义 IEC 标准单位（二进制，基数为 1024）
    const units = ["B", "K", "M", "G", "T", "P", "E", "Z", "Y"]
    const k = 1024

    // 6. 计算应该使用的单位索引
    const i = Math.floor(Math.log(bytes) / Math.log(k))

    // 7. 格式化数值并拼接单位（保留两位小数，并去掉末尾多余的 0）
    const formattedValue = parseFloat((bytes / Math.pow(k, i)).toFixed(1))
    return `${formattedValue} ${units[i]}`
}

function subStrIn(needle, haystack) {
    if (typeof needle !== "string" || typeof haystack !== "string") {
        return false // 如果输入的不是字符串，直接返回 false
    }

    needle = needle.replace(/ /g, "")
    if (needle === "") {
        return true // 空字符串默认是任何字符串的子序列
    }

    if (haystack === "") {
        return false // b 为空且 a 不为空时，肯定不包含
    }

    needle = needle.toLowerCase()
    haystack = haystack.toLowerCase()

    let i = 0 // 指向 s1 的指针
    let j = 0 // 指向 s2 的指针

    // 只要两个字符串都没遍历完，就继续比对
    while (i < needle.length && j < haystack.length) {
        // 如果当前字符匹配，s1 的指针向后移动
        if (needle[i] === haystack[j]) {
            i++
        }
        // 无论是否匹配，s2 的指针都要向后移动，继续寻找下一个字符
        j++
    }

    // 如果 i 等于 s1 的长度，说明 s1 中的所有字符都按顺序在 s2 中找到了
    return i === needle.length
}

function stripSpace(str) {
    if (!str) {
        return ""
    }
    return str.replace(/\s/g, "")
}

function flattenObject(obj, res = {}) {
    for (let key in obj) {
        if (typeof obj[key] === "object" && obj[key] !== null && !Array.isArray(obj[key])) {
            flattenObject(obj[key], res)
        } else {
            res[key] = obj[key]
        }
    }
    return res
}

function formatJsonKeys(obj, indentLevel = 0) {
    // 如果不是对象或者是 null，返回空字符串
    if (typeof obj !== "object" || obj === null) {
        return ""
    }

    let result = ""
    const indent = " ".repeat(indentLevel * 4)

    for (const key in obj) {
        if (obj.hasOwnProperty(key)) {
            // 拼接当前 key 和换行符
            result += `${indent}${key}\n`

            // 如果是嵌套对象，递归获取子层的字符串并拼接
            if (typeof obj[key] === "object" && obj[key] !== null) {
                result += formatJsonKeys(obj[key], indentLevel + 1)
            }
        }
    }

    return result
}

function formatJson(o) {
    return JSON.stringify(o, null, "  ")
}

function clone(o) {
    return JSON.parse(JSON.stringify(o))
}

function compareString(a, b) {
    return a.localeCompare(b, "en", { sensitivity: "base" })
}

function sortInPlace(list, key, isNumber, isDescending) {
    const rev = isDescending ? (cond) => -1 * cond : (cond) => cond
    const comp = isNumber
        ? (d1, d2) => d1[key] - d2[key]
        : (d1, d2) => compareString(d1[key], d2[key])
    const mond = (d1, d2) => rev(comp(d1, d2))
    list.sort(mond)
}

function getValue(dict, expKey, defKey) {
    return dict[expKey] || dict[defKey]
}

function uniqueByKey(arr, key) {
    return [...new Map(arr.map((d) => [d[key], d])).values()]
}

async function copyToClipboard(...args) {
    const textToCopy = args.join("\n")
    try {
        await navigator.clipboard.writeText(textToCopy)
        return true
    } catch (err) {
        return false
    }
}

export default {
    log,
    i18n,
    isDevEnv,

    query, // query(data, url, timeout, method)
    gotoPage,
    reloadPage,
    clone,
    formatBytes,
    formatJson,
    formatJsonKeys,
    subStrIn,
    buildFilters,
    stripSpace,
    compareString,
    sortInPlace,
    getValue,
    isIPv4Addr,
    parseIPv4Addr,
    extractIPv4Addr,
    uniqueByKey,
    copyToClipboard,
    flattenObject,

    getCurThemeMode,
    switchTheme,
    getCurLangName,
    switchLanguage,
}
