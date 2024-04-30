<template>
    <button
        :class="{
            'flex items-center gap-1': true,
            'flex-row-reverse': !isLeftOpp,
        }"
    >
        <span
            :class="{
                'py-1 rounded-full text-sm flex items-center justify-center transition border-[3px]': true,
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

        <div @click.stop>
            <AudioChat
                v-if="isSelf && room && userId"
                :participants="room.participants"
                :room-id="room.id"
                :is-self="isSelf"
                :user-id="userId"
            />

            <Microphone
                v-if="streamId"
                :user-id="userId"
                :stream-id="streamId"
                :is-self="isSelf"
            />
        </div>
    </button>
</template>

<script setup lang="ts">
import { ChevronDownIcon } from "heroicons-vue3/solid";
import UserCardScore from "./UserCardScore.vue";
import { PropType } from "vue";
import AudioChat from "./AudioChat.vue";
import Microphone from "./Microphone.vue";
import { Room } from "../store/authStore";

const props = defineProps({
    name: String,
    friend: Boolean,
    active: null,
    showMenu: Boolean,
    score: Number,
    scoreDiff: null,
    senior: Boolean,
    room: Object as PropType<Room>,
    isSelf: Boolean,
    isLeftOpp: Boolean,
    userId: Number,
    streamId: String,
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
