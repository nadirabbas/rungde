<template>
    <div>
        <template v-if="generalStore.started">
            <div
                class="z-10 fixed w-full h-full left-0 top-0 flex items-center justify-center bg-white"
                v-show="generalStore.loading"
            >
                <Spinner />
            </div>

            <div
                class="portrait-only w-full min-h-screen items-center justify-center"
            >
                <p class="text-2xl">Please rotate your device</p>
            </div>
            <div class="landscape-only">
                <component :is="layout">
                    <router-view />
                </component>
            </div>
        </template>

        <Start @start="generalStore.started = true" v-else />
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import Spinner from "./components/Spinner.vue";
import { useAuthStore } from "./store/authStore";
import { useGeneralStore } from "./store/generalStore";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import AuthLayout from "./layouts/AuthLayout.vue";
import { api } from "./api";
import { useRoute, useRouter } from "vue-router";
import BlankLayout from "./layouts/BlankLayout.vue";
import Start from "./views/Start.vue";

const generalStore = useGeneralStore();
const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const layout = computed(() => {
    if (route.meta.blank) return BlankLayout;
    if (route.meta.layout) {
        const layout = {
            auth: AuthLayout,
        }[route.meta.layout as string];
        if (layout) return layout;
    }

    return authStore.user ? DefaultLayout : AuthLayout;
});

const verifyAuth = async () => {
    if (authStore.user) {
        generalStore.loading = true;
        try {
            const res = await api.get("/me").then((res) => res.data);
            authStore.login(res.user);

            if (res.user.room?.is_ended === false && route.name !== "Game") {
                router.push({
                    name: "Game",
                });
            }
        } catch (err) {
            authStore.logout();
            router.push({
                name: "Login",
            });
            console.error(err);
        }
        generalStore.loading = false;
    }
};
window.addEventListener("keydown", (e) => {
    if (e.key === "*") {
        authStore.logout();
        router.push({
            name: "Login",
        });
    }
});

onMounted(async () => {
    verifyAuth();
});
</script>
