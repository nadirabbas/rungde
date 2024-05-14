<template>
    <span
        :class="{
            'rounded-full aspect-square flex items-center justify-center': true,
            'rd-bg text-white': dark,
            'bg-white text-secondary': !dark,
        }"
        :style="{
            background: avatar ? `url(${avatar}) center / cover` : undefined,
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
