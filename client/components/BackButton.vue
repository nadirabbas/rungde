<template>
    <button
        @click="$router.back()"
        :class="{
            'text-sm rounded flex items-center gap-1 max-w-max px-3 p-1': true,
            'text-black bg-white': light,
            'text-white rd-bg': !light,
        }"
    >
        <ArrowLeftIcon class="w-4" />
        <span class="leading-none">Back</span>
    </button>
</template>

<script setup lang="ts">
import { ArrowLeftIcon } from "heroicons-vue3/solid";

defineProps({
    light: Boolean,
});
</script>
