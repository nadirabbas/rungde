<template>
    <div class="flex items-stretch w-full gap-2 mt-6">
        <button
            class="rounded bg-red-600 text-white py-2 px-3"
            @click="$emit('cancel')"
        >
            <XIcon class="w-4" />
        </button>

        <button
            class="w-full flex-1 text-sm rounded px-3 py-2 rd-bg text-white"
            @click="$emit('confirm')"
            :disabled="disabled"
        >
            <slot />
        </button>
    </div>
</template>

<script setup lang="ts">
import { XIcon } from "heroicons-vue3/solid";

defineProps({
    disabled: Boolean,
});
</script>
