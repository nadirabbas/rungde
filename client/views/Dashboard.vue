<template>
    <div class="flex flex-col gap-3">
        <form @submit.prevent="() => join()">
            <FieldLabel label="Room code" class="mb-3">
                <TextField v-model="roomCode" />
            </FieldLabel>
            <div class="flex items-center gap-3">
                <Button :loading="joining" class="flex-1 rd-bg text-white"
                    >Join room</Button
                >
                <Button
                    type="button"
                    @click="joinAsSpectator"
                    :loading="joining"
                    class="rd-bg-2 flex-1 text-white"
                    >Spectate</Button
                >
            </div>
        </form>

        <div class="relative py-3">
            <p class="text-center text-2xl font-medium text-primary">OR</p>
        </div>

        <Button
            :loading="creating"
            class="rd-bg text-white w-full"
            @click="create"
            >Create room</Button
        >
    </div>
</template>

<script setup lang="ts">
import { api } from "../api";
import Button from "../components/Button.vue";
import FieldLabel from "../components/FieldLabel.vue";
import TextField from "../components/TextField.vue";
import { usePusher } from "../composables/usePusher";
import { onMounted, ref } from "vue";
import { useAuthStore } from "../store/authStore";
import { useRouter } from "vue-router";
import { useToast } from "../composables/useToast";
import { useGeneralStore } from "../store/generalStore";

const authStore = useAuthStore();

const roomCode = ref("");

const creating = ref(false);
const joining = ref(false);
const router = useRouter();

const create = async () => {
    creating.value = true;

    try {
        const res = await api.post("/rooms").then((res) => res.data);
        authStore.setRoom(res.room);
        router.push({
            name: "Game",
            params: {
                id: res.room.id,
            },
        });
    } catch (err) {
        console.error(err);
    }

    creating.value = false;
};

const toast = useToast();

const join = async (asSpectator = false) => {
    let error = "";
    if (!roomCode.value) error = "Please enter a room code";
    if (error) {
        toast.error(error);
        return;
    }

    joining.value = true;

    try {
        const res = await api.put("/rooms/join", {
            code: roomCode.value,
            as_spectator: asSpectator ? 1 : 0,
        });
        authStore.setRoom(res.data.room);
        router.push({
            name: "Game",
            params: {
                id: res.data.room.id,
            },
        });
    } catch (err) {
        console.error(err);
    }

    joining.value = false;
};

const joinAsSpectator = () => {
    return join(true);
};
</script>
