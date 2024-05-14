<template>
    <div>
        <Modal
            @close="loading ? null : $emit('close')"
            :title="isSelf ? 'Options' : 'Player: ' + user?.user.username"
            :loading="loading"
            :model-value="modelValue"
        >
            <button
                :class="buttonClass('bg-green-600 text-white mb-2')"
                @click="copyCode(room.code)"
                v-if="isSelf"
            >
                Copy room code: {{ room.code }}
            </button>

            <button
                :class="
                    buttonClass(
                        `rd-bg text-white mb-2 ${
                            muteMap[user.user_id] && 'opacity-50'
                        }`
                    )
                "
                @click="toggleMute"
                v-if="!isSelf"
            >
                <MutableIcon :muted="muteMap[user.user_id]">
                    <VolumeUpIcon class="w-4" />
                </MutableIcon>
                <span class="leading-none">{{
                    muteMap[user.user_id] ? "User muted" : "Mute"
                }}</span>
            </button>

            <button
                :class="
                    buttonClass(
                        `rd-bg text-white mb-2 ${
                            muteEmojiMap[user.user_id] && 'opacity-50'
                        }`
                    )
                "
                @click="toggleEmojiMute"
                v-if="!isSelf"
            >
                <MutableIcon :muted="muteEmojiMap[user.user_id]">
                    <EmojiHappyIcon class="w-4" />
                </MutableIcon>
                <span class="leading-none">{{
                    muteEmojiMap[user.user_id] ? "Emojis muted" : "Mute emojis"
                }}</span>
            </button>

            <button
                :class="buttonClass('rd-bg mb-2 text-white')"
                @click="viewProfile"
            >
                View profile
            </button>

            <button
                :class="buttonClass('bg-red-600 text-white')"
                @click="kickUser"
                v-if="!isSelf && isHost"
            >
                Kick user
            </button>

            <button
                :class="buttonClass('rd-bg mb-2 text-white')"
                @click="restartRoom()"
                v-if="isHost && isSelf"
            >
                Restart game
            </button>

            <button
                :class="buttonClass('rd-bg mb-2 text-white')"
                @click="restartRoom(false, true)"
                v-if="isHost && isSelf"
            >
                Clear total score
            </button>

            <button
                :class="buttonClass('bg-red-600 text-white')"
                @click="isHost ? closeRoom() : leaveRoom()"
                v-if="isSelf"
            >
                {{ isHost ? "Close" : "Leave" }} room
            </button>
        </Modal>

        <ProfileModal v-model="viewUsername" />
    </div>
</template>

<script lang="ts">
export const buttonClass = (c: string) =>
    `py-3 shadow-xl leading-none px-8 rounded text-base flex items-center justify-center gap-2 ${c} w-full`;
</script>

<script setup lang="ts">
import { PropType, ref, toRefs } from "vue";
import { RoomUser } from "../store/authStore";
import { api } from "../api";
import { useRouter } from "vue-router";
import Modal from "./Modal.vue";
import ProfileModal from "../components/ProfileModal.vue";
import copy from "copy-to-clipboard";
import { useToast } from "../composables/useToast";
import { useBus } from "../composables/useBus";
import MutableIcon from "./MutableIcon.vue";
import {
    EmojiHappyIcon,
    MicrophoneIcon,
    VolumeUpIcon,
} from "heroicons-vue3/solid";

const emit = defineEmits(["close", "restart", "update:muteEmojiMap"]);

const props = defineProps({
    user: {
        type: null as any as PropType<RoomUser>,
        required: true,
    },
    isSelf: Boolean,
    isHost: Boolean,
    modelValue: Boolean,
    restartFn: {
        type: Function,
        required: true,
    },
    room: null,
    muteMap: {
        type: Object as PropType<{ [key: string]: boolean }>,
        required: true,
    },
    muteEmojiMap: {
        type: Object as PropType<{ [key: string]: boolean }>,
        required: true,
    },
});

const { user, muteMap, muteEmojiMap } = toRefs(props);

const bus = useBus();
const toggleMute = () => {
    bus.emit(
        muteMap.value[user.value.user_id] ? "unmute-user" : "mute-user",
        user.value.user_id
    );
};
const toggleEmojiMute = () => {
    emit("update:muteEmojiMap", {
        ...muteEmojiMap.value,
        [user.value.user_id]: !muteEmojiMap.value[user.value.user_id],
    });
};

const loading = ref(false);

const apiReq = async (uri: string, data: any, cb: () => void) => {
    loading.value = true;

    try {
        await api.put(uri, data).then(() => cb());
    } catch (err) {
        console.error(err);
    }

    loading.value = false;
};

const toast = useToast();
const copyCode = (code: string) => {
    try {
        copy(code);
        toast.success("Code copied to clipboard");
    } catch (error) {
        toast.error("There was a problem copying the code");
    }
};

const leaveRoom = async () => {
    apiReq("/room/leave", {}, () => {
        window.location.href = "/";
    });
};
const closeRoom = async () => {
    apiReq("/room/close", {}, () => {
        emit("close");
        window.location.href = "/";
    });
};
const kickUser = async () => {
    apiReq("/room/kick", { position: user.value.position }, () => {
        emit("close");
    });
};

const restartRoom = async (resetScores = false, resetScoreOnly = false) => {
    loading.value = true;
    await props.restartFn(resetScores, resetScoreOnly);
    emit("close");
    loading.value = false;
};

const viewUsername = ref("");
bus.on("view-profile", (username: any) => {
    viewUsername.value = username;
});
const viewProfile = () => {
    emit("close");
    viewUsername.value = user.value.user.username;
};
</script>
