<template>
    <span
        class="bg-white rounded-full aspect-square flex items-center justify-center"
        :style="{
            background: `background: url(${avatar}) center / cover`,
            width: `${width}px`,
        }"
    >
        <UserIcon
            v-show="!avatar"
            class="text-secondary"
            :style="`width: ${width / 1.5}px`"
        />
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
});
</script>
