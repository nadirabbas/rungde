<template>
    <div
        class="w-full min-h-screen rd-bg-pattern lg:flex-col gap-10 flex items-center justify-center"
    >
        <Logo class="fixed top-5 left-5 w-[100px]" />
        <Navbar class="fixed top-5 right-5" v-if="authStore.user" />

        <div class="max-w-full min-w-[85%] md:min-w-[50%] lg:min-w-[400px]">
            <div class="flex items-center justify-between relative mb-5">
                <div
                    id="back"
                    class="absolute top-1/2 left-0 -translate-y-1/2"
                ></div>
                <h1
                    id="title"
                    class="text-center text-2xl font-medium w-full text-white leading-none"
                ></h1>
                <div
                    id="filters"
                    class="absolute top-1/2 right-0 -translate-y-1/2"
                ></div>
            </div>

            <div class="rounded bg-white p-3">
                <RouterView />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Logo from "../components/Logo.vue";
import { useAuthStore } from "../store/authStore";
import Navbar from "../components/Navbar.vue";

const authStore = useAuthStore();
</script>
