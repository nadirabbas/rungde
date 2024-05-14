<template>
    <div>
        <TransitionFade>
            <div
                class="fixed z-40 left-0 top-0 w-full h-full bg-black bg-opacity-95 flex items-center justify-center cursor-pointer"
                @click.self="isOpen = false"
                v-if="isOpen"
            >
                <div class="grid grid-cols-9 gap-5">
                    <button
                        v-for="reaction in reactions"
                        :key="reaction"
                        @click="sendReaction(reaction)"
                    >
                        <img
                            :src="animationData(reaction, true)"
                            class="w-[50px] aspect-square"
                        />
                    </button>
                </div>

                <button
                    @click="isOpen = false"
                    class="text-white absolute right-5 top-5"
                >
                    <XIcon class="w-9" />
                </button>

                <p
                    class="fixed bottom-5 text-base text-gray-500 hidden lg:block"
                >
                    Shortcut: E
                </p>
            </div>
        </TransitionFade>

        <MountedTeleport to="#spectator-reactions">
            <div class="flex flex-col gap-2 mt-2">
                <div
                    v-for="(reaction, id) in spectatorReactions"
                    :class="spectatorActionClass()"
                    :key="id"
                >
                    <p class="text-sm font-medium text-white">
                        @{{ reaction.username }}
                    </p>

                    <Vue3Lottie
                        :animation-link="reaction.reaction"
                        :key="id"
                        :loop="reaction.loops"
                        @onComplete="() => removeSpecReaction(id)"
                        :width="25"
                        :height="25"
                    />
                </div>

                <div
                    v-for="(_, specId) in speakerMap"
                    :class="spectatorActionClass()"
                    :key="specId"
                >
                    <p class="text-sm font-medium text-white">
                        @{{ spectatorMap[specId].user.username }}
                    </p>

                    <MicrophoneIcon class="w-4 text-white" />
                </div>
            </div>
        </MountedTeleport>
    </div>
</template>

<script setup lang="ts">
import { Vue3Lottie } from "vue3-lottie";
import { useBus } from "../composables/useBus";
import { MicrophoneIcon } from "heroicons-vue3/solid";
import {
    PropType,
    onMounted,
    reactive,
    ref,
    toRefs,
    watch,
    watchEffect,
} from "vue";
import { Room, User } from "../store/authStore";
import { Channel } from "pusher-js";
import { XIcon } from "heroicons-vue3/solid";
import { TransitionFade } from "@morev/vue-transitions";
import { api } from "../api";
import { useMagicKeys } from "@vueuse/core";
import { v4 as uuid } from "uuid";
import MountedTeleport from "./MountedTeleport.vue";
import { useSoundSprite } from "../composables/useSoundSprite";

const spectatorActionClass = (c) =>
    `${c} flex items-center justify-between gap-2 py-1 px-3 bg-black bg-opacity-40 rounded-full`;

const bus = useBus();

const props = defineProps({
    room: {
        type: Object as PropType<Room>,
        required: true,
    },
    channel: {
        type: Object as PropType<Channel>,
        required: true,
    },
    user: {
        type: Object as PropType<User>,
        required: true,
    },
    isSpectating: Boolean,
    spectatorMap: Object as PropType<Record<string, any>>,
});

const { channel, user, isSpectating, spectatorMap } = toRefs(props);

const isOpen = ref(false);

bus.on("open-reactions", () => {
    isOpen.value = true;
});

const specialLoops = {
    "23f0": 4,
    "44f": 6,
    "923": 4,
    aab: 1,
    2753: 1,
    "44b": 4,
};

const reactions = [
    "44b",
    "4af",
    525,
    "2764_fe0f",
    494,
    2753,
    "4aa",
    "44f",
    "44d",
    "44e",
    "64f",
    "23f0",
    604,
    601,
    "2639_fe0f",
    "ae3",
    971,
    928,
    621,
    605,
    602,
    923,
    "62d",
    "92f",
    "60e",
    609,
    618,
    "60d",
    973,
    979,
    "61c",
    614,
    611,
    "ae1",
    914,
    "92b",
];

const animationData = (reaction: string | number, img = false) => {
    const len = reaction.toString().length;
    let name;

    if (len === 3) {
        name = "1f" + reaction;
    } else {
        name = reaction;
    }

    return `https://fonts.gstatic.com/s/e/notoemoji/latest/${name}/${
        img ? "emoji.svg" : "lottie.json"
    }`;
};

const loading = ref(false);
const isAnimationBeingPlayed = ref(false);
const { play } = useSoundSprite();

bus.on("animation-complete", (userId: any) => {
    if (userId !== user.value.id) return;
    isAnimationBeingPlayed.value = false;
});

const sendReaction = async (reaction: string) => {
    isOpen.value = false;

    if (loading.value) {
        return;
    }

    isAnimationBeingPlayed.value = true;
    bus.emit("reaction-sent", {
        user_id: user.value.id,
        reaction: animationData(reaction),
        loops: specialLoops[reaction] || 2,
    });

    loading.value = true;

    try {
        channel.value.trigger("client-reaction", {
            reaction,
            user_id: user.value.id,
            username: user.value.username,
            isSpec: isSpectating.value,
        });
    } catch (error) {
        console.error(error);
    }

    loading.value = false;
};

reactions.forEach((reaction) => {
    const link = document.createElement("link");
    link.rel = "prefetch";
    link.href = animationData(reaction);
    document.head.appendChild(link);
});

const spectatorReactions = reactive({});
const removeSpecReaction = (id) => {
    delete spectatorReactions[id];
};

const speakerMap = ref<Record<string, any>>({});
bus.on("speaking", (userId: any) => {
    if (!spectatorMap?.value?.[userId]) return;
    speakerMap.value[userId] = true;
});

bus.on("quiet", (userId: any) => {
    if (!spectatorMap?.value?.[userId]) return;
    delete speakerMap.value[userId];
});

onMounted(() => {
    channel.value.bind(
        "client-reaction",
        ({ user_id, reaction, username, isSpec }) => {
            if (user_id === user.value.id) return;

            if (isSpec) {
                const reactionId = uuid();
                spectatorReactions[reactionId] = {
                    username,
                    reaction: animationData(reaction),
                    loops: specialLoops[reaction] || 2,
                };
                play({ id: "reaction" });
                return;
            }

            bus.emit("reaction-sent", {
                user_id,
                reaction: animationData(reaction),
                loops: specialLoops[reaction] || 2,
            });
        }
    );
});

const { e } = useMagicKeys();
watchEffect(() => {
    if (!e.value || isAnimationBeingPlayed.value) return;
    isOpen.value = !isOpen.value;
});
</script>
