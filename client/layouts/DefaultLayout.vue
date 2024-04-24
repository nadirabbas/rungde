<template>
    <div
        class="w-full min-h-screen rd-bg-pattern lg:flex-col gap-10 flex items-center justify-center"
    >
        <Logo class="w-[200px]" />

        <div class="w-[50%] lg:w-[400px] max-w-full">
            <div class="rounded bg-white p-3"><slot /></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
import Logo from "../components/Logo.vue";
import { useAuthStore } from "../store/authStore";
import { api } from "../api";
const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
    try {
        await api.post("/auth/logout");
        authStore.logout();
        router.push({
            name: "Login",
        });
    } catch (err) {}
};
</script>
