import { useAuthStore } from "../store/authStore";
import { routes } from "./routes";
import { createRouter, createWebHistory } from "vue-router";

export const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = !!authStore.user;

    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: "Login" });
    } else if (to.meta.redirectIfAuth && isAuthenticated) {
        next({ name: "Dashboard" });
    } else {
        next();
    }
});
