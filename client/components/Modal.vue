<template>
    <TransitionFade>
        <div
            class="fixed top-0 left-0 w-screen h-screen flex items-center justify-center backdrop-blur-sm bg-black bg-opacity-70"
            @click.self="$emit('close')"
            v-if="modelValue"
        >
            <div
                class="bg-white rounded p-5 w-[25vw] flex flex-col items-center text-2xl"
            >
                <h1
                    :class="{
                        'w-full text-black text-center text-lg': true,
                        'mb-6': !loading,
                    }"
                >
                    <template v-if="loading">Please wait...</template>
                    <template v-else>{{ title }}</template>
                </h1>

                <slot v-if="!loading" />
            </div>
        </div>
    </TransitionFade>
</template>

<script setup lang="ts">
import { TransitionFade } from "@morev/vue-transitions";

defineProps({
    title: String,
    loading: Boolean,
    modelValue: Boolean,
});
</script>
