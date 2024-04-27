<template>
    <span
        :class="{
            'rounded-full w-6 font-bold flex items-center justify-center h-5 text-black bg-white': true,
        }"
        >{{ score }}</span
    >
</template>

<script setup lang="ts">
import { PropType } from "vue";

defineProps({
    score: Number,
    friend: Boolean,
});
</script>
