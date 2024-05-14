<template>
    <span
        :class="{
            'rounded-full aspect-square flex items-center justify-center': true,
            'bg-white text-secondary': dark,
            'rd-bg text-white': !dark,
        }"
        :style="{
            backgroundImage: `url(${avatar}) center / cover`,
            width: `${width}px`,
        }"
    >
        <UserIcon v-show="!avatar" :style="`width: ${width / 1.5}px`" />
    </span>
</template>

<script setup lang="ts">
import { UserIcon } from "heroicons-vue3/solid";

defineProps({
    avatar: null,
    width: {
        type: Number,
        default: 40,
    },
    dark: Boolean,
});
</script>
