<template>
    <TransitionFade>
        <div
            class="fixed z-40 left-0 top-0 w-full h-full bg-black bg-opacity-80 flex items-center justify-center"
            v-if="isOpen"
        >
            <div class="grid grid-cols-8 gap-5">
                <button
                    v-for="reaction in reactions"
                    :key="reaction"
                    @click="sendReaction(reaction)"
                >
                    <Vue3Lottie
                        :animation-link="animationData(reaction)"
                        :width="50"
                        :height="50"
                    />
                </button>
            </div>

            <button
                @click="isOpen = false"
                class="text-white absolute right-5 top-5"
            >
                <XIcon class="w-7" />
            </button>
        </div>
    </TransitionFade>
</template>

<script setup lang="ts">
import { Vue3Lottie } from "vue3-lottie";
import { useBus } from "../composables/useBus";
import { PropType, onMounted, ref, toRefs, watch } from "vue";
import { Room, User } from "../store/authStore";
import { Channel } from "pusher-js";
import { XIcon } from "heroicons-vue3/solid";
import { TransitionFade } from "@morev/vue-transitions";
import { api } from "../api";

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

const reactions = [
    "2764_fe0f",
    494,
    "44f",
    "44d",
    "44e",
    "64f",
    "23f0",
    600,
    604,
    601,
    971,
    928,
    644,
    621,
    "92c",
    605,
    602,
    923,
    622,
    "62d",
    "92f",
    "60e",
    609,
    618,
    "60d",
    929,
    973,
    979,
    "60b",
    "61c",
    614,
    611,
    "ae1",
    914,
    "92b",
];

const animationData = (reaction: string | number) => {
    const len = reaction.toString().length;
    let name;

    if (len === 3) {
        name = "1f" + reaction;
    } else {
        name = reaction;
    }

    return `https://fonts.gstatic.com/s/e/notoemoji/latest/${name}/lottie.json`;
};

const loading = ref(false);
const sendReaction = async (reaction: string) => {
    isOpen.value = false;

    if (loading.value) {
        return;
    }

    bus.emit("reaction-sent", {
        user_id: user.value.id,
        reaction: animationData(reaction),
    });

    loading.value = true;

    try {
        await api.post("/reaction", { reaction });
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
    channel.value.bind("reaction", ({ user_id, reaction }) => {
        if (user_id === user.value.id) return;
        bus.emit("reaction-sent", {
            user_id,
            reaction: animationData(reaction),
        });
    });
});
</script>
