<template>
    <div class="flex items-center">
        <span
            :class="{
                'w-8 h-8 rounded-full text-white text-sm flex items-center justify-center ': true,
                ' bg-green-500': friend,
                ' bg-red-500': !friend,
                'border-[3px] border-white shadow-lg golden-glow': active,
            }"
        >
            {{ capital(name ? name[0] : "?") }}
        </span>

        <div class="flex-1 ml-1">
            <h3
                :class="{
                    'font-semibold': true,
                    'text-green-500': friend,
                    'text-red-500': !friend,
                }"
            >
                {{ name || "Waiting for user..." }}
            </h3>
        </div>
    </div>
</template>

<script setup lang="ts">
import { capital } from "case";
import { ArrowRightIcon, ArrowLeftIcon } from "heroicons-vue3/solid";

const turnArrow = (c: string) => `p-1 rounded-full bg-white ${c}`;

const props = defineProps({
    name: String,
    friend: Boolean,
    active: null,
});
</script>

<style lang="scss"></style>
