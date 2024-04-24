<template>
    <div
        class="rd-bg w-screen h-screen flex items-center justify-center flex-col"
    >
        <Logo class="w-[200px] mb-10" />

        <Button @click="start">Start</Button>
    </div>
</template>

<script setup lang="ts">
import { useFullscreen, useScreenOrientation } from "@vueuse/core";
import { ref } from "vue";
import Logo from "../components/Logo.vue";
import Button from "../components/Button.vue";

const emit = defineEmits(["start"]);

const el = ref(document.documentElement);
const { lockOrientation, isSupported } = useScreenOrientation();
const { enter, isFullscreen } = useFullscreen(el);

const start = async () => {
    try {
        if (!isFullscreen.value) await enter();
        if (isSupported) await lockOrientation("landscape");
    } catch (err) {}

    emit("start");
};
</script>
