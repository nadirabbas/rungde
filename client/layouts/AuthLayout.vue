<template>
    <div
        class="w-full min-h-screen flex-col rd-bg-pattern flex items-center justify-center gap-10"
    >
        <Logo class="w-[200px]" />

        <div class="min-w-[85%] md:min-w-[50%] lg:min-w-[400px] max-w-full">
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
import Logo from "../components/Logo.vue";
</script>
