<template>
    <button class="flex items-center">
        <span
            :class="{
                'py-1 rounded-full  text-sm flex items-center justify-center transition border-[3px]': true,
                ' bg-green-600': friend && name,
                ' bg-red-600': !friend && name,
                'bg-gray-500 border-gray-500': !name,
                'border-green-600': friend && !senior && name,
                'border-red-600': !friend && !senior && name,
                'user-card-border': active && !senior,
                'user-card-border-senior': active && senior,
                'px-3': !showMenu,
                'pr-2': showMenu,
                'pl-3': showMenu && !score,
                'pl-1': score,
                'text-white': !senior,
                'bg-yellow font-bold text-black border-yellow': senior,
            }"
        >
            <UserCardScore
                :score="scoreDiff ? `+${scoreDiff}` : score"
                class="mr-2"
                v-if="score"
            />

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
    scoreDiff: Number,
    senior: Boolean,
});
</script>

<style lang="scss">
.user-card-border {
    animation: border-pulse 1s infinite;
}

.user-card-border-senior {
    animation: border-pulse-alt 1s infinite;
}

@keyframes border-pulse {
    50% {
        border: 3px solid white;
    }
}

@keyframes border-pulse-alt {
    50% {
        border: 3px solid white;
        background: white;
    }
}
</style>
