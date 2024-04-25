<template>
    <div
        class="fixed top-0 left-0 w-screen h-screen flex items-center justify-center"
        @click.self="loading ? null : $emit('close')"
    >
        <div
            class="bg-black bg-opacity-90 rounded p-5 w-[25vw] flex flex-col items-center text-white text-2xl"
        >
            <div
                :class="{
                    'w-full text-white text-center text-lg': true,
                    'mb-6': !loading,
                }"
            >
                <template v-if="loading">Please wait...</template>

                <template v-else>{{
                    isSelf ? "Options" : "Player: " + user.user.username
                }}</template>
            </div>

            <template v-if="!loading">
                <button
                    :class="buttonClass('bg-red-500')"
                    @click="kickUser"
                    v-if="!isSelf"
                >
                    Kick user
                </button>

                <button
                    :class="buttonClass('bg-red-500')"
                    @click="isHost ? closeRoom() : leaveRoom()"
                    v-if="isSelf"
                >
                    {{ isHost ? "Close" : "Leave" }} room
                </button>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { PropType, ref, toRefs } from "vue";
import { RoomUser } from "../store/authStore";
import { api } from "../api";
import { useRouter } from "vue-router";

const emit = defineEmits(["close"]);

const buttonClass = (c: string) => `py-1 px-8 rounded text-base ${c}`;

const props = defineProps({
    user: {
        type: Object as PropType<RoomUser>,
        required: true,
    },
    isSelf: Boolean,
    isHost: Boolean,
});

const { user } = toRefs(props);

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

const router = useRouter();

const leaveRoom = async () => {
    apiReq("/room/leave", {}, () => {
        window.location.href = "/";
    });
};
const closeRoom = async () => {
    apiReq("/room/close", {}, () => {
        window.location.href = "/";
    });
};
const kickUser = async () => {
    apiReq("/room/kick", { position: user.value.position }, () => {
        emit("close");
    });
};
</script>
