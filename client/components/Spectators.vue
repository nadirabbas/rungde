<template>
    <div style="z-index: 999">
        <button
            :class="{
                'p-1 rounded-full bg-yellow relative text-[#222]': true,
                'opacity-50': !room.spectators.length,
            }"
            @click="room.spectators.length && (isOpen = !isOpen)"
        >
            <EyeIcon class="w-6 h-6" />
            <span
                :class="{
                    'bg-red-500 text-white font-bold rounded-full absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center text-sm': true,
                }"
                v-show="room.spectators.length"
                >{{ room.spectators.length }}</span
            >
        </button>

        <Modal v-model="isOpen" title="Spectators">
            <div class="w-full min-w-[60vw]">
                <div
                    v-for="s in room.spectators"
                    class="rd-bg p-2 rounded mb-2 flex justify-between"
                >
                    <div class="flex items-center gap-2">
                        <Avatar :avatar="s.user.avatar" :width="30" />
                        <p class="text-base text-white">
                            @{{ s.user.username }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="toggleMute(s.user.id)"
                            :class="
                                spectatorActionClass(
                                    `${muteMap[s.user.id] && 'opacity-50'}`
                                )
                            "
                        >
                            <MicrophoneMutable
                                class="w-4"
                                :muted="muteMap[s.user.id]"
                            />
                        </button>

                        <button
                            @click="kickSpectator(s.id)"
                            v-if="room.user_id == authStore.user.id"
                            :class="spectatorActionClass()"
                        >
                            <XIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import { PropType, ref, toRefs } from "vue";
import { Room, useAuthStore } from "../store/authStore";
import { EyeIcon, MicrophoneIcon, XIcon } from "heroicons-vue3/solid";
import Modal from "./Modal.vue";
import { api } from "../api";
import Avatar from "./Avatar.vue";
import { useBus } from "../composables/useBus";
import MicrophoneMutable from "./MicrophoneMutable.vue";

const spectatorActionClass = (c) =>
    `${c} text-[#222] bg-white p-1 rounded-full`;

const isOpen = ref(false);
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
});

const { muteMap } = toRefs(props);

const bus = useBus();
const toggleMute = (userId: number) => {
    bus.emit(muteMap.value[userId] ? "unmute-user" : "mute-user", userId);
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
</script>
