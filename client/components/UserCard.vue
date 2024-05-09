<template>
    <button
        :class="{
            'flex gap-1 items-center z-40 user-card': true,
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

        <div
            @click.stop
            v-if="generalStore.hasUserInteracted"
            class="items-center flex"
        >
            <AudioChat
                v-if="isSelf && room && userId && render"
                :participants="room.participants"
                :room-id="room.id"
                :is-self="isSelf"
                :user-id="userId"
                @reinit="reinitAudioChat"
            />

            <Microphone
                v-if="render && userId"
                :user-id="userId"
                :is-self="isSelf"
                :hide-for-others="!!reactionSent"
                v-model="isSpeaking"
                :class="{
                    'absolute top/1-2 -translated-y-1/2': !isSelf,
                    '-translate-x-full': isTeammate,
                    'translate-x-full right-0': isLeftOpp,
                    '-translate-x-full left-0':
                        !isSelf && !isTeammate && !isLeftOpp,
                }"
            />
        </div>

        <div
            v-if="reactionSent"
            :class="{
                absolute: true,
                '-bottom-2 -translate-x-full': isSelf && !score,
                '-translate-x-full -top-3': isTeammate && !score,

                '-bottom-2 -translate-x-[120%]': isSelf && score,
                '-top-3 -translate-x-[120%]': isTeammate && score,

                'translate-x-full right-0': isLeftOpp,
                '-translate-x-full left-0':
                    !isSelf && !isTeammate && !isLeftOpp,
            }"
        >
            <Vue3Lottie
                :animation-link="reactionSent"
                :width="80"
                :height="80"
            />
        </div>

        <MountedTeleport to="#communications">
            <button
                @click="openEmoji"
                class="p-1 bg-yellow rounded-full text-[#222]"
                v-if="isSelf"
            >
                <EmojiHappyIcon
                    :class="{
                        'w-6': true,
                    }"
                />
            </button>
        </MountedTeleport>

        <ClockIcon
            class="animate-pulse w-7 text-white"
            v-show="showClock && !reactionSent"
        />
    </button>
</template>

<script setup lang="ts">
import { ChevronDownIcon } from "heroicons-vue3/solid";
import { EmojiHappyIcon } from "heroicons-vue3/solid";
import UserCardScore from "./UserCardScore.vue";
import { PropType, nextTick, ref, toRefs } from "vue";
import MountedTeleport from "./MountedTeleport.vue";
import AudioChat from "./AudioChat.vue";
import Microphone from "./Microphone.vue";
import { Room, useAuthStore } from "../store/authStore";
import { useGeneralStore } from "../store/generalStore";
import { ClockIcon } from "heroicons-vue3/outline";
import { useBus } from "../composables/useBus";
import { Vue3Lottie } from "vue3-lottie";
import { useSoundSprite } from "../composables/useSoundSprite";

const { play } = useSoundSprite();

const generalStore = useGeneralStore();

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
    isTeammate: Boolean,
    showClock: Boolean,
});

const { userId } = toRefs(props);

const isSpeaking = ref(false);
const render = ref(true);
const reinitAudioChat = () => {
    render.value = false;
    nextTick(() => {
        render.value = true;
    });
};

const bus = useBus();
const reactionSent = ref("");
bus.on("reaction-sent", (reaction: any) => {
    if (reaction.user_id != userId?.value) return;

    play({ id: "reaction" });
    reactionSent.value = reaction.reaction;

    setTimeout(() => {
        reactionSent.value = "";
    }, 4000);
});
const openEmoji = () => {
    if (reactionSent.value) return;
    bus.emit("open-reactions");
};
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
