import { reactive } from "vue"

export const store = reactive({ servAddr: "" })

export function setServAddr(addr) {
    store.servAddr = addr
}
