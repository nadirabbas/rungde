<template>
    <TransitionFade>
        <div
            class="fixed top-0 left-0 w-screen h-screen flex items-center justify-center backdrop-blur-sm bg-black bg-opacity-70"
            @click.self="close"
            style="z-index: 9999"
            v-if="modelValue"
        >
            <div
                :class="`bg-white rounded min-w-[30vw] flex flex-col items-center text-2xl max-h-[95vh] overflow-y-auto ${
                    bodyClass || 'p-5'
                }`"
            >
                <div
                    :class="{
                        'w-full flex flex-col justify-center items-center relative': true,
                        'mb-6': !loading,
                    }"
                    v-if="!hideTitle"
                >
                    <h1
                        :class="{
                            ' leading-none text-black text-center text-lg': true,
                        }"
                    >
                        <template v-if="loading">Please wait...</template>
                        <template v-else>{{ title }}</template>
                    </h1>

                    <p
                        class="leading-none text-gray-500 text-sm absolute right-0 top-1/2 -translate-y-1/2 hidden lg:block"
                        v-if="hint"
                    >
                        {{ hint }}
                    </p>
                </div>

                <slot v-if="!loading" />

                <p
                    v-if="subtitle"
                    class="leading-none text-xs text-gray-500 mt-2"
                >
                    {{ subtitle }}
                </p>
            </div>
        </div>
    </TransitionFade>
</template>

<script setup lang="ts">
import { TransitionFade } from "@morev/vue-transitions";

const emit = defineEmits(["update:modelValue", "close"]);

defineProps({
    title: String,
    subtitle: String,
    loading: Boolean,
    modelValue: Boolean,
    bodyClass: String,
    hideTitle: Boolean,
    hint: String,
});

const close = () => {
    emit("update:modelValue", false);
    emit("close");
};
</script>
