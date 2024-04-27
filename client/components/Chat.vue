<template>
    <div>
        <button
            class="fixed right-5 top-20 rounded-full bg-yellow text-[#222] p-1"
            @click="open"
        >
            <ChatAlt2Icon class="w-6 h-6" @click="open" />
            <span
                v-show="hasUnread"
                class="bg-red-500 text-white rounded-full w-3 h-3 block absolute -top-0.5 -right-0.5"
            ></span>
        </button>

        <Modal
            hide-title
            title="Chat"
            v-model="isOpen"
            body-class="min-h-[75vh]"
        >
            <div class="flex-1 w-full" v-if="messages.length">
                <div
                    class="flex mb-0 text-base"
                    v-for="m in messages"
                    :key="m.id"
                >
                    <strong>{{ m.username }}</strong>
                    <p class="ml-2">{{ m.msg }}</p>
                </div>
            </div>

            <div class="flex-1 flex items-center justify-center" v-else>
                <p class="text-base text-gray-500">No messages</p>
            </div>

            <div class="flex items-stretch gap-1 mt-4">
                <input
                    class="w-full border border-black rounded px-2 py-0.5 text-base"
                    placeholder="Type a message..."
                    v-model="message"
                />
                <button
                    @click="send"
                    :disabled="!message || loading"
                    class="text-base rounded w-9 flex items-center text-white justify-center bg-green-600"
                >
                    <template v-if="loading">...</template>
                    <template v-else>
                        <PaperAirplaneIcon class="w-4 rotate-90" />
                    </template>
                </button>
            </div>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import { PropType, onMounted, ref, toRefs } from "vue";
import { Room } from "../store/authStore";
import { Channel } from "pusher-js";
import Modal from "./Modal.vue";
import { v4 } from "uuid";
import { api } from "../api";
import { ChatAlt2Icon, PaperAirplaneIcon } from "heroicons-vue3/solid";
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

onMounted(() => {
    channel.value.bind(
        "chat",
        ({ msg, username: un }: { msg: string; username: string }) => {
            if (un === username.value) return;
            if (!isOpen.value) {
                hasUnread.value = true;
                play({ id: "newMessage" });
            }
            console.log(msg);
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
</script>
