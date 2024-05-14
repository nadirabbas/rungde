<template>
    <div style="z-index: 999">
        <button
            :class="{
                'p-1 rounded-full bg-yellow relative text-[#222]': true,
            }"
            @click="isOpen = !isOpen"
        >
            <EyeIcon class="w-6 h-6" />
            <span
                :class="{
                    'bg-red-500 text-white font-bold rounded-full absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center text-sm': true,
                }"
                v-show="spectators.length"
                >{{ spectators.length }}</span
            >
        </button>

        <Modal
            v-model="isOpen"
            :title="
                requestedSwapFor
                    ? 'Swap places'
                    : incomingSwapRequestBy
                    ? 'Swap requested'
                    : 'Spectators'
            "
            :subtitle="
                room.user_id == authStore.user.id && !requestedSwapFor
                    ? 'You can mute for yourself only, not for everyone.'
                    : ''
            "
        >
            <div
                :class="{
                    'w-full': true,
                    'min-w-[60vw]': !requestedSwapFor,
                }"
            >
                <SpectatorSwap
                    :requested-for="requestedSwapFor"
                    :incoming-request-by="incomingSwapRequestBy"
                    :channel="channel"
                    @close="
                        () => {
                            requestedSwapFor = null;
                            incomingSwapRequestBy = null;
                        }
                    "
                    v-if="requestedSwapFor || incomingSwapRequestBy"
                />

                <div v-else>
                    <div
                        class="absolute top-4 right-5 flex items-center justify-center"
                        v-if="
                            room.user_id != authStore.user.id && !isSpectating
                        "
                    >
                        <button
                            class="text-sm bg-green-600 text-white p-1 rounded-full px-3"
                            @click="switchToSpectator"
                        >
                            {{
                                switching
                                    ? "Please wait..."
                                    : "Switch to Spectator"
                            }}
                        </button>
                    </div>

                    <div
                        v-if="!spectators.length"
                        class="flex items-center justify-center min-h-[100px] text-sm text-gray-600"
                    >
                        No spectators
                    </div>

                    <div
                        v-for="s in spectators"
                        class="rd-bg p-2 rounded mb-2 flex justify-between"
                    >
                        <div class="flex items-center gap-2">
                            <Avatar :avatar="s.user.avatar" :width="30" />
                            <p class="text-base text-white">
                                @{{ s.user.username }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <template v-if="s.user.id != authStore.user.id">
                                <button
                                    v-if="!isSpectating"
                                    @click="requestedSwapFor = s.user"
                                    :class="
                                        spectatorActionClass(
                                            'text-[#222] bg-white'
                                        )
                                    "
                                >
                                    <SwitchHorizontalIcon class="w-5" />
                                </button>

                                <button
                                    @click="viewProfile(s.user.username)"
                                    :class="
                                        spectatorActionClass(
                                            'text-[#222] bg-white'
                                        )
                                    "
                                >
                                    <UserIcon class="w-5" />
                                </button>

                                <button
                                    @click="toggleEmojiMute(s.user.id)"
                                    :class="
                                        spectatorActionClass(
                                            `${
                                                muteEmojiMap[s.user.id] &&
                                                'opacity-50'
                                            } text-[#222] bg-white`
                                        )
                                    "
                                >
                                    <MutableIcon
                                        :muted="muteEmojiMap[s.user.id]"
                                    >
                                        <EmojiHappyIcon class="w-5" />
                                    </MutableIcon>
                                </button>

                                <button
                                    @click="toggleMute(s.user.id)"
                                    :class="
                                        spectatorActionClass(
                                            `${
                                                muteMap[s.user.id] &&
                                                'opacity-50'
                                            } text-[#222] bg-white`
                                        )
                                    "
                                >
                                    <MutableIcon :muted="muteMap[s.user.id]">
                                        <VolumeUpIcon class="w-5" />
                                    </MutableIcon>
                                </button>
                            </template>

                            <button
                                @click="kickSpectator(s.id)"
                                v-if="room.user_id == authStore.user.id"
                                :class="
                                    spectatorActionClass(
                                        'text-white bg-red-500'
                                    )
                                "
                            >
                                <XIcon class="w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import { PropType, computed, onMounted, onUnmounted, ref, toRefs } from "vue";
import { Room, RoomUser, User, useAuthStore } from "../store/authStore";
import {
    EmojiHappyIcon,
    EyeIcon,
    MicrophoneIcon,
    SpeakerphoneIcon,
    SwitchHorizontalIcon,
    UserIcon,
    VolumeUpIcon,
    XIcon,
} from "heroicons-vue3/solid";
import Modal from "./Modal.vue";
import { api } from "../api";
import Avatar from "./Avatar.vue";
import { useBus } from "../composables/useBus";
import MutableIcon from "./MutableIcon.vue";
import SpectatorSwap from "./SpectatorSwap.vue";
import { Channel } from "pusher-js";

const spectatorActionClass = (c) => `${c} p-1.5 rounded-full`;

const requestedSwapFor = ref();
const incomingSwapRequestBy = ref();

const isOpen = ref(false);
const switching = ref(false);
const authStore = useAuthStore();

const props = defineProps({
    room: {
        type: Object as PropType<Room>,
        required: true,
    },
    muteMap: {
        type: Object as PropType<Record<string, boolean>>,
        required: true,
    },
    muteEmojiMap: {
        type: Object as PropType<Record<string, boolean>>,
        required: true,
    },
    channel: {
        type: Object as PropType<Channel>,
        required: true,
    },
    isSpectating: Boolean,
    spectatorMap: {
        type: Object as PropType<Record<string, RoomUser>>,
        required: true,
    },
});

const { muteMap, muteEmojiMap, channel, spectatorMap } = toRefs(props);

const spectators = computed(() => Object.values(spectatorMap.value));

const bus = useBus();

const viewProfile = (username: string) => {
    isOpen.value = false;
    bus.emit("view-profile", username);
};

const toggleMute = (userId: number) => {
    bus.emit(muteMap.value[userId] ? "unmute-user" : "mute-user", userId);
};
const emit = defineEmits(["update:muteEmojiMap"]);
const toggleEmojiMute = (userId: number) => {
    emit("update:muteEmojiMap", {
        ...muteEmojiMap.value,
        [userId]: !muteEmojiMap.value[userId],
    });
};

const spectatorsBeingKicked = ref<Record<string, boolean>>({});

const kickSpectator = async (spectatorId) => {
    if (spectatorsBeingKicked.value[spectatorId]) return;
    spectatorsBeingKicked.value[spectatorId] = true;
    try {
        await api.put("/room/kick-spectator", {
            spectator_id: spectatorId,
        });
    } catch (error) {
        console.error(error);
    }
    delete spectatorsBeingKicked.value[spectatorId];
};

onMounted(() => {
    channel.value.bind(
        "client-places-swapped",
        ({ involved }: { involved: number[] }) => {
            if (involved.includes(authStore.user?.id || 0)) {
                isOpen.value = false;
                requestedSwapFor.value = null;
                incomingSwapRequestBy.value = null;
            }
        }
    );

    channel.value.bind(
        "client-swap",
        ({ user, forId }: { user: User; forId: number }) => {
            if (forId != authStore.user?.id) return;
            incomingSwapRequestBy.value = user;
            isOpen.value = true;
        }
    );

    channel.value.bind(
        "client-swap-deny",
        ({ user, forId }: { user: User; forId: number }) => {
            if (forId != authStore.user?.id) return;
            requestedSwapFor.value = null;
        }
    );
});

onUnmounted(() => {
    channel.value.unbind("client-swap");
    channel.value.unbind("client-swap-deny");
});

const switchToSpectator = async () => {
    switching.value = true;
    try {
        await api.post("/room/switch-to-spectator");
    } catch (error) {
        console.error(error);
    }
    switching.value = false;
};
</script>
