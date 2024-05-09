<template>
    <div
        class="w-full min-h-screen rd-bg-pattern lg:flex-col gap-10 flex items-center justify-center"
    >
        <Logo class="fixed top-5 left-5 w-[100px]" />
        <Navbar class="fixed top-5 right-5" v-if="authStore.user" />

        <div class="max-w-full min-w-[50%] lg:min-w-[400px]">
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
