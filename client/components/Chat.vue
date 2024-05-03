<template>
    <div>
        <button
            class="fixed right-36 top-5 rounded-full bg-yellow text-[#222] p-1"
            @click="open"
        >
            <ChatAlt2Icon class="w-6 h-6" @click="open" />
            <span
                v-show="hasUnread"
                class="bg-red-500 text-white rounded-full w-3 h-3 block absolute -top-0.5 -right-0.5"
            ></span>
        </button>

        <TransitionSlide
            :offset="{
                enter: [300, 0],
                leave: [300, 0],
            }"
        >
            <div
                class="fixed flex flex-col right-0 top-0 h-screen w-[30vw] max-w-full z-40"
                v-if="isOpen"
            >
                <div class="py-1 relative bg-[#18181b]">
                    <button
                        @click="isOpen = false"
                        class="flex left-1 top-1/2 -translate-y-1/2 absolute items-center justify-center rounded-full w-7 h-7 text-white"
                    >
                        <XIcon class="w-8" />
                    </button>

                    <p class="leading-0 text-center text-white text-lg">Chat</p>
                </div>

                <div
                    class="flex-1 relative h-[65vh] max-h-full w-full bg-opacity-95 bg-[#18181b] overflow-y-auto p-3"
                    ref="chatDiv"
                >
                    <div class="flex-1 w-full relative" v-if="messages.length">
                        <div
                            class="mb-1 text-base"
                            v-for="m in messages"
                            :key="m.id"
                        >
                            <strong
                                class="font-bold"
                                :style="`color: ${getUserColor(m.username)}`"
                                >{{ m.username }}:</strong
                            >
                            <span class="ml-2 text-white">{{ m.msg }}</span>
                        </div>

                        <div
                            class="flex justify-center my-1 bottom-0 absolute left-1/2 -translate-y-1/2 text-red-500"
                            v-if="hasUnread"
                        >
                            <ChevronDownIcon class="w-4" />
                        </div>
                    </div>

                    <div
                        class="absolute left-0 top-0 w-full h-full flex items-center justify-center"
                        v-else
                    >
                        <p class="text-base text-gray-500">No messages</p>
                    </div>
                </div>

                <div class="flex flex-col w-full">
                    <form
                        @submit.prevent="send"
                        class="flex items-stretch w-full pt-0"
                    >
                        <input
                            ref="messageInput"
                            class="focus:outline-none w-full border border-black p-2 text-base"
                            placeholder="Type a message..."
                            v-model="message"
                            :readonly="loading"
                        />
                        <button
                            type="submit"
                            :disabled="!message || loading"
                            class="text-base w-9 flex items-center text-white justify-center bg-primary"
                        >
                            <template v-if="loading">...</template>
                            <template v-else>
                                <PaperAirplaneIcon class="w-4 rotate-90" />
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </TransitionSlide>
    </div>
</template>

<script setup lang="ts">
import {
    PropType,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
    toRefs,
    watch,
} from "vue";
import { Room } from "../store/authStore";
import { Channel } from "pusher-js";
import Modal from "./Modal.vue";
import { v4 } from "uuid";
import { api } from "../api";
import {
    ChatAlt2Icon,
    ChevronDownIcon,
    PaperAirplaneIcon,
    XIcon,
} from "heroicons-vue3/solid";
import { useSound } from "@vueuse/sound";
import { TransitionSlide } from "@morev/vue-transitions";

const props = defineProps({
    room: {
        type: Object as PropType<Room>,
        required: true,
    },
    channel: {
        type: null as any as PropType<Channel>,
        required: true,
    },
    username: {
        type: String,
        required: true,
    },
});

const messages = ref<
    {
        id: string;
        username: string;
        msg: string;
    }[]
>([]);

const colors = [
    "hotpink",
    "springgreen",
    "#359bff", // blue
    "#ffe700", // cyan
];
const lastSelectedColorIdx = ref(0);
const usernameColorMap = ref<Record<string, string>>({});
const getUserColor = (username: string) => {
    if (usernameColorMap.value[username]) {
        return usernameColorMap.value[username];
    }
    const color = colors[lastSelectedColorIdx.value];
    lastSelectedColorIdx.value =
        (lastSelectedColorIdx.value + 1) % colors.length;
    usernameColorMap.value[username] = color;
    return color;
};

const { channel, username } = toRefs(props);

const isOpen = ref(false);
const message = ref("");
const loading = ref(false);
const hasUnread = ref(false);

const { play } = useSound("/audio/sprite.opus", {
    // @ts-ignore
    sprite: {
        newMessage: [23781, 24848 - 23781],
    },
});

const altCHandler = (e: KeyboardEvent) => {
    if (e.key === "c" && e.altKey) {
        isOpen.value = !isOpen.value;
    }
};

const escHandler = (e: KeyboardEvent) => {
    if (e.key === "Escape") {
        isOpen.value = false;
    }
};

const chatDiv = ref<HTMLDivElement | null>(null);
onUnmounted(() => {
    try {
        window.removeEventListener("keydown", altCHandler);
        window.removeEventListener("keydown", escHandler);
    } catch (error) {}
});
onMounted(() => {
    try {
        window.removeEventListener("keydown", altCHandler);
        window.removeEventListener("keydown", escHandler);
    } catch (error) {}
    window.addEventListener("keydown", altCHandler);
    window.addEventListener("keydown", escHandler);

    channel.value.bind(
        "chat",
        ({ msg, username: un }: { msg: string; username: string }) => {
            const isSelf = un === username.value;
            if (isSelf) return;
            if (!isOpen.value) {
                hasUnread.value = true;
                play({ id: "newMessage" });
            }
            messages.value.push({ msg, username: un, id: v4() });
        }
    );
});

const send = async () => {
    if (!message.value || !username.value) return;

    loading.value = true;

    try {
        await api.put("/room/chat", {
            msg: message.value,
            username: username.value,
        });

        messages.value.push({
            msg: message.value,
            username: username.value,
            id: v4(),
        });

        message.value = "";
    } catch (error) {}

    loading.value = false;
};

const open = () => {
    hasUnread.value = false;
    isOpen.value = true;
};

const messageInput = ref<HTMLInputElement | null>(null);
watch(isOpen, (val) => {
    nextTick(() => {
        if (messageInput.value) {
            messageInput.value.focus();
        }
        if (chatDiv.value) {
            chatDiv.value.scrollTop = chatDiv.value.scrollHeight;
        }
    });
});

watch(
    () => messages.value.length,
    () => {
        nextTick(() => {
            const lastestMessage = messages.value[messages.value.length - 1];
            const isSelf = lastestMessage.username === username.value;
            if (chatDiv.value) {
                const val =
                    chatDiv.value.scrollHeight -
                    chatDiv.value.scrollTop -
                    chatDiv.value.clientHeight;
                const hasScrolledUp = false; /*val > 24*/

                if (hasScrolledUp) {
                    if (!isSelf) hasUnread.value = true;
                } else {
                    chatDiv.value.scrollTop = chatDiv.value.scrollHeight;
                }
            }
        });
    }
);
</script>
