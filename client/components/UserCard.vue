<template>
    <button
        :class="{
            'flex gap-1 user-card': true,
            'flex-row-reverse': !isLeftOpp,
            'items-end': isSelf,
            'flex-col justify-center items-center':
                isTeammate || isSpectatorCard,
            'flex-col-reverse': isSpectatorCard,
            'items-center': isLeftOpp || (!isSelf && !isTeammate && !isLeftOpp),
        }"
        @click="!name && isSpectating && switchToPlayer()"
    >
        <div
            :class="{
                'flex items-center gap-1': true,
                'flex-row-reverse': !isLeftOpp,
            }"
        >
            <span
                :class="{
                    'py-1 rounded-full text-sm flex items-center justify-center transition border-[3px]': true,
                    ' bg-green-600': friend && name,
                    ' bg-red-600': !friend && !isSpectatorCard && name,
                    'bg-gray-500 border-gray-500': !name && !isSpectating,
                    'bg-yellow font-bold border-yellow': !name && isSpectating,
                    'border-green-600': friend && !senior && name,
                    'border-red-600':
                        !friend && !senior && name && !isSpectatorCard,
                    'user-card-border': active && !senior && !isSpectatorCard,
                    'user-card-border-senior':
                        active && senior && !isSpectatorCard,
                    'px-3': !showMenu,
                    'pr-2': showMenu,
                    'pl-3': showMenu && !score,
                    'pl-1': score,
                    'text-white':
                        !senior && !isSpectatorCard && (!isSpectating || name),
                    'bg-yellow font-bold text-black border-yellow': senior,
                    'min-w-32 min-h-10': large,
                    'border-white bg-white text-black font-bold':
                        isSpectatorCard,
                }"
            >
                <UserCardScore
                    :score="scoreDiff ? `+${scoreDiff}` : score"
                    class="mr-2"
                    v-if="score"
                />

                <span>
                    {{
                        name ||
                        (isSpectating ? "Join seat" : "Waiting for user...")
                    }}
                </span>

                <ChevronDownIcon class="ml-1 w-4" v-if="showMenu" />
            </span>

            <div
                @click.stop
                v-if="generalStore.hasUserInteracted"
                class="items-center flex"
            >
                <AudioChat
                    v-if="isSelf && room && userId && render && !hideVoiceChat"
                    :participants="room.participants"
                    :room-id="room.id"
                    :is-self="isSelf"
                    :user-id="userId"
                    @update:mute-map="$emit('update:mute-map', $event)"
                    :mute-map="muteMap"
                    @update:mute-emoji-map="
                        $emit('update:mute-emoji-map', $event)
                    "
                    :mute-emoji-map="muteEmojiMap"
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
                        '-translate-x-[103.5%]': isTeammate,
                        'translate-x-[103.5%] right-0': isLeftOpp,
                        '-translate-x-[103.5%] left-0':
                            !isSelf && !isTeammate && !isLeftOpp,
                    }"
                />
            </div>
        </div>

        <div
            v-if="reactionSent"
            :class="{
                '-mb-1': isSelf && !isSpectatorCard,
            }"
        >
            <Vue3Lottie
                :animation-link="reactionSent"
                :width="60"
                :height="60"
                :loop="loops"
                @onComplete="onAnimationComplete"
            />
        </div>

        <MountedTeleport to="#em" v-if="!hideEmoji">
            <button
                @click="openEmoji"
                :class="{
                    'p-1 bg-yellow rounded-full text-[#222]': true,
                    'opacity-50': reactionSent,
                }"
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
import { api } from "../api";

const { play } = useSoundSprite();

const generalStore = useGeneralStore();

const props = defineProps({
    name: String,
    position: Number,
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
    large: Boolean,
    hideEmoji: Boolean,
    hideVoiceChat: Boolean,
    isSpectatorCard: Boolean,
    isSpectating: Boolean,
    muteMap: null,
    muteEmojiMap: null,
});

const { userId, position } = toRefs(props);

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
const loops = ref(2);

const onAnimationComplete = () => {
    reactionSent.value = "";
    bus.emit("animation-complete", userId?.value);
};

bus.on("reaction-sent", (reaction: any) => {
    if (reaction.user_id != userId?.value) return;

    play({ id: "reaction" });
    loops.value = reaction.loops;
    reactionSent.value = reaction.reaction;
});
const openEmoji = () => {
    if (reactionSent.value) return;
    bus.emit("open-reactions");
};

const switchToPlayer = async () => {
    generalStore.loading = true;
    try {
        await api.post("/room/switch-to-player", {
            position: position?.value,
        });
    } catch (error) {
        console.error(error);
    }
    generalStore.loading = false;
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
