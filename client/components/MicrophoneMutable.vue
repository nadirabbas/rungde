<template>
    <div class="relative">
        <MicrophoneIcon class="w-full" />
        <TransitionFade>
            <span
                class="top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 lg:w-5 bg-black absolute rotate-45 w-[125%] h-[10%]"
                v-if="muted"
            ></span>
        </TransitionFade>
    </div>
</template>

<script setup lang="ts">
import { TransitionFade } from "@morev/vue-transitions";
import { MicrophoneIcon } from "heroicons-vue3/solid";
defineProps({
    muted: Boolean,
});
</script>
