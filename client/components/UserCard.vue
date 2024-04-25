<template>
    <button class="flex items-center">
        <span
            :class="{
                'py-1 rounded-full  text-sm flex text-white items-center justify-center ': true,
                ' bg-green-500': friend && name,
                ' bg-red-500': !friend && name,
                'bg-gray-500': !name,
                'border-[3px] border-[#ffd700]': active,
                'px-3': !showMenu,
                'pl-3 pr-2': showMenu,
            }"
        >
            <span>
                {{ name || "Waiting for user..." }}
            </span>

            <ChevronDownIcon class="ml-1 w-4" v-if="showMenu" />
        </span>
    </button>
</template>

<script setup lang="ts">
import { capital } from "case";
import { ChevronDownIcon } from "heroicons-vue3/solid";

const turnArrow = (c: string) => `p-1 rounded-full bg-white ${c}`;

const props = defineProps({
    name: String,
    friend: Boolean,
    active: null,
    showMenu: Boolean,
});
</script>

<style lang="scss"></style>
