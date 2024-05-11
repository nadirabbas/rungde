<template>
    <div>
        <button
            class="p-1 rounded-full bg-yellow relative text-[#222]"
            @click="room.spectators.length && (isOpen = !isOpen)"
        >
            <EyeIcon class="w-6 h-6" />
            <span
                class="bg-red-600 text-white font-bold rounded-full absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center text-sm"
                >{{ room.spectators.length }}</span
            >
        </button>

        <Modal v-model="isOpen" title="Spectators">
            <div class="w-full">
                <div
                    v-for="s in room.spectators"
                    class="rd-bg p-2 rounded mb-2 text-center relative"
                >
                    <p class="text-base text-white">@{{ s.user.username }}</p>

                    <button
                        @click="kickSpectator(s.id)"
                        v-if="room.user_id == authStore.user.id"
                        class="absolute top-1/2 -translate-y-1/2 right-3 text-white"
                    >
                        <XIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import { PropType, ref } from "vue";
import { Room, useAuthStore } from "../store/authStore";
import { EyeIcon, XIcon } from "heroicons-vue3/solid";
import Modal from "./Modal.vue";
import { api } from "../api";

const isOpen = ref(false);
const authStore = useAuthStore();

const props = defineProps({
    room: {
        type: Object as PropType<Room>,
        required: true,
    },
});

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
