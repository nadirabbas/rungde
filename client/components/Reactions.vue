<template>
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

            <p class="fixed bottom-5 text-base text-gray-500 hidden lg:block">
                Shortcut: E
            </p>
        </div>
    </TransitionFade>
</template>

<script setup lang="ts">
import { Vue3Lottie } from "vue3-lottie";
import { useBus } from "../composables/useBus";
import { PropType, onMounted, ref, toRefs, watch, watchEffect } from "vue";
import { Room, User } from "../store/authStore";
import { Channel } from "pusher-js";
import { XIcon } from "heroicons-vue3/solid";
import { TransitionFade } from "@morev/vue-transitions";
import { api } from "../api";
import { useMagicKeys } from "@vueuse/core";

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
});

const { channel, user } = toRefs(props);

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
};

const reactions = [
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
    "aab",
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

onMounted(() => {
    channel.value.bind("client-reaction", ({ user_id, reaction }) => {
        if (user_id === user.value.id) return;
        bus.emit("reaction-sent", {
            user_id,
            reaction: animationData(reaction),
            loops: specialLoops[reaction] || 2,
        });
    });
});

const { e } = useMagicKeys();
watchEffect(() => {
    if (!e.value || isAnimationBeingPlayed.value) return;
    isOpen.value = !isOpen.value;
});
</script>
