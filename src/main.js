import "@/assets/base.css"

import { createApp } from "vue"
import App from "./App.vue"
import router from "./router"
import utils from "./libs/utils.js"

const app = createApp(App)

app.use(utils.i18n)
app.use(router)

app.mount("#app")
