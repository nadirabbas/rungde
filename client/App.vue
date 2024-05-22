<template>
    <FullscreenProvider>
        <div
            class="fixed w-full h-full left-0 top-0 flex items-center justify-center bg-white"
            v-show="generalStore.loading"
            style="z-index: 999"
        >
            <Spinner />
        </div>

        <div class="landscapse-only">
            <component :is="layout" :key="layout">
                <router-view :key="layout" />
            </component>
        </div>
    </FullscreenProvider>
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
import { useWakeLock } from "@vueuse/core";
import { useToast } from "./composables/useToast";
import FullscreenProvider from "./components/FullscreenProvider.vue";

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

            if (res.room && route.name !== "Game") {
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

const { request, release, isSupported } = useWakeLock();
const toast = useToast();

onMounted(async () => {
    verifyAuth();
    if (isSupported.value) {
        request("screen");
    }

    const queryParams = new URLSearchParams(window.location.search);
    if (queryParams.get("room_closed")) {
        toast.error("Room has been closed");
    }
});

onUnmounted(() => {
    release();
});

window.addEventListener(
    "click",
    () => {
        generalStore.hasUserInteracted = true;
    },
    { once: true }
);
</script>
