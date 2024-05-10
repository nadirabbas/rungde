<template>
    <ul class="flex items-center gap-2">
        <li v-for="item in navItems" :key="item.label">
            <router-link
                :to="item.to"
                :class="{
                    'flex items-center gap-2 p-1.5 lg:px-3 lg:py-1 rounded-full lg:rounded text-secondary bg-white': true,
                    hidden: item.to === route.path,
                }"
            >
                <component :is="item.icon" class="w-5 h-5" />
                <span class="hidden lg:block">{{ item.label }}</span>
            </router-link>
        </li>
    </ul>
</template>

<script setup lang="ts">
import { CogIcon, ChartBarIcon } from "heroicons-vue3/solid";
import { markRaw } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();

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
];
</script>
