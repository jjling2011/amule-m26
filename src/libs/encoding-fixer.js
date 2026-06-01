import { createIconv, encodings, aliases } from "@/libs/iconv-tiny.mjs"

const iconv = createIconv(encodings, aliases)

function shorter(s, str) {
    return s.length < str.length
}

function encode(s, encoding) {
    const b1 = iconv.encode(s, "utf8")
    const s2 = iconv.decode(b1, encoding)
    return s2
}

function shrink(prev) {
    let cur = ""
    while (true) {
        try {
            const buff = iconv.encode(prev, "latin1")
            cur = iconv.decode(buff, "utf8")
            if (shorter(cur, prev)) {
                prev = cur
            } else {
                break
            }
        } catch {
            return prev
        }
    }
    return prev
}

function fixCjkStr(str) {
    const encodings = [
        // "gb18030", // gb1803 > gbk > GB2312(euc-cn),
        // "big5",
        // "eucjp",
        "shiftjis",
        // "euckr",
    ]
    const cache = [str]
    for (let encoding of encodings) {
        try {
            const buffer = iconv.encode(str, encoding)
            const s1 = iconv.decode(buffer, "utf8")
            if (shorter(s1, str)) {
                cache.push(s1)
            }
            const s2 = fixUtf8Char(s1)
            if (shorter(s2, str)) {
                cache.push(s2)
            }
        } catch {}
    }
    if (cache.length > 1) {
        cache.sort((a, b) => a.length - b.length)
    }
    return cache[0]
}

function autoFixString(prev, raw) {
    if (typeof prev !== "string" || prev === "") {
        return prev
    }

    const s1 = fixUtf8Char(prev)
    const s2 = fixCjkStr(s1)
    return raw ? s2 : s2.replaceAll("�", "")
}

function fixUtf8Char(str) {
    const fragments = []
    const len = str.length
    for (let i = 0; i < len; ) {
        const start = i
        if (str.charCodeAt(i) <= 255) {
            while (i < len && str.charCodeAt(i) <= 255) i++
            const s1 = str.slice(start, i)
            // const s2 = Buffer.from(s1, "latin1").toString()
            const s2 = new TextDecoder("latin1").decode(
                Uint8Array.from([...s1].map((char) => char.charCodeAt(0))),
            )

            fragments.push(s2.length < s1.length ? s2 : s1)
        } else {
            while (i < len && str.charCodeAt(i) > 255) i++
            fragments.push(str.slice(start, i))
        }
    }

    const s1 = fragments.join("")
    return shrink(s1)
    // return s1
}

export default {
    encode,
    autoFixString,
}
