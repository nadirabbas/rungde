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

        <Modal hide-title title="Chat" v-model="isOpen" body-class="p-0">
            <div class="flex flex-col w-full lg:px-3">
                <div
                    class="relative h-[65vh] max-h-full w-full overflow-y-auto py-4 px-2"
                    ref="chatDiv"
                >
                    <div class="flex-1 w-full relative" v-if="messages.length">
                        <div
                            class="flex mb-0 text-base"
                            v-for="m in messages"
                            :key="m.id"
                        >
                            <strong>{{ m.username }}</strong>
                            <p class="ml-2">{{ m.msg }}</p>
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

                <form
                    @submit.prevent="send"
                    class="flex items-stretch gap-1 w-full p-2 pt-0"
                >
                    <input
                        ref="messageInput"
                        class="w-full border border-black rounded py-1.5 px-2 text-base"
                        placeholder="Type a message..."
                        v-model="message"
                        :readonly="loading"
                    />
                    <button
                        type="submit"
                        :disabled="!message || loading"
                        class="text-base rounded w-12 flex items-center text-white justify-center bg-green-600"
                    >
                        <template v-if="loading">...</template>
                        <template v-else>
                            <PaperAirplaneIcon class="w-4 rotate-90" />
                        </template>
                    </button>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import { PropType, nextTick, onMounted, ref, toRefs, watch } from "vue";
import { Room } from "../store/authStore";
import { Channel } from "pusher-js";
import Modal from "./Modal.vue";
import { v4 } from "uuid";
import { api } from "../api";
import {
    ChatAlt2Icon,
    ChevronDownIcon,
    PaperAirplaneIcon,
} from "heroicons-vue3/solid";
import { useSound } from "@vueuse/sound";

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

const chatDiv = ref<HTMLDivElement | null>(null);
onMounted(() => {
    try {
        window.removeEventListener("keydown", altCHandler);
    } catch (error) {}
    window.addEventListener("keydown", altCHandler);

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
