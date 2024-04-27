import { createApp } from "vue";
import App from "./App.vue";
import { createPinia } from "pinia";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import { router } from "./router/router";
import ToastPlugin from "vue-toast-notification";
import "./assets/css/tailwind.css";
import "./assets/css/main.scss";
import "./assets/css/toast.scss";

const app = createApp(App);

// Pinia
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);
app.use(pinia);

// Router
app.use(router);
app.use(ToastPlugin);
app.mount("#app");
