<template>
    <div>
        <div class="landscape-only">
            <slot />
        </div>

        <div
            class="portrait-only fixed top-0 left-0 h-screen w-screen rd-bg-pattern flex flex-col items-center justify-center gap-4"
        >
            <div
                class="max-w-[70%] md:max-w-[50%] flex flex-col items-center gap-4"
            >
                <Logo class="w-[140px] mb-10" />
                <p class="text-white font-medium text-center">
                    Fullscreen is required to play the game.
                </p>
                <button
                    @click="goFullscreen"
                    class="bg-white text-[#222] px-3 py-2 rounded font-medium"
                >
                    Go fullscreen
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useFullscreen, useScreenOrientation } from "@vueuse/core";
import Logo from "./Logo.vue";

const { isSupported, enter } = useFullscreen();
const { isSupported: isOrientationSupported, lockOrientation } =
    useScreenOrientation();

const goFullscreen = () => {
    if (!isOrientationSupported.value || !isSupported.value) return;
    enter();
    lockOrientation("landscape-primary");
};
</script>
