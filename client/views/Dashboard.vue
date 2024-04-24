<template>
    <div class="flex flex-col gap-3">
        <form @submit.prevent="join">
            <FieldLabel label="Room code" class="mb-3">
                <TextField v-model="roomCode" />
            </FieldLabel>
            <Button :loading="joining" class="rd-bg text-white w-full"
                >Join room</Button
            >
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

const join = async () => {
    let error = "";
    if (!roomCode.value) error = "Please enter a room code";
    if (error) {
        alert(error);
        return;
    }

    joining.value = true;

    try {
        const res = await api.put("/rooms/join", {
            code: roomCode.value,
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
</script>
