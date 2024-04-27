<template>
    <button class="flex items-center">
        <span
            :class="{
                'py-1 rounded-full  text-sm flex text-white items-center justify-center ': true,
                ' bg-green-600': friend && name,
                ' bg-red-600': !friend && name,
                'bg-gray-500': !name,
                'border-[3px] border-white': active,
                'px-3': !showMenu,
                'pl-3 pr-2': showMenu,
                'pl-1': score,
            }"
        >
            <UserCardScore :score="score" class="mr-2" v-if="score" />

            <span>
                {{ name || "Waiting for user..." }}
            </span>

            <ChevronDownIcon class="ml-1 w-4" v-if="showMenu" />
        </span>
    </button>
</template>

<script setup lang="ts">
import { ChevronDownIcon } from "heroicons-vue3/solid";
import UserCardScore from "./UserCardScore.vue";

const props = defineProps({
    name: String,
    friend: Boolean,
    active: null,
    showMenu: Boolean,
    score: Number,
});
</script>

<style lang="scss"></style>
