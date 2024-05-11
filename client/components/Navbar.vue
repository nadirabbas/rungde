<template>
    <ul class="flex items-center gap-2">
        <li v-for="item in navItems" :key="item.label">
            <component
                :is="item.to ? 'router-link' : 'button'"
                @click="item.click"
                :to="item.to"
                :class="{
                    'flex items-center gap-2 p-1.5 lg:px-3 lg:py-1 rounded-full lg:rounded text-secondary bg-white': true,
                    hidden: item.to === route.path,
                }"
            >
                <component :is="item.icon" class="w-5 h-5" />
                <span class="hidden lg:block">{{ item.label }}</span>
            </component>
        </li>
    </ul>
</template>

<script setup lang="ts">
import { CogIcon, ChartBarIcon, LogoutIcon } from "heroicons-vue3/solid";
import { markRaw } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../store/authStore";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const navItems = [
    {
        label: "Match history",
        icon: markRaw(ChartBarIcon),
        to: "/global-match-history",
    },
    {
        label: "Profile",
        icon: markRaw(CogIcon),
        to: "/profile",
    },
    {
        label: "Logout",
        icon: markRaw(LogoutIcon),
        click() {
            window.location.href = "/login";
            authStore.logout();
        },
    },
];
</script>
