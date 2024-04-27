<template>
    <Modal
        @close="loading ? null : $emit('close')"
        :title="isSelf ? 'Options' : 'Player: ' + user?.user.username"
        :loading="loading"
        :model-value="modelValue"
    >
        <button
            :class="buttonClass('bg-red-600 text-white')"
            @click="kickUser"
            v-if="!isSelf"
        >
            Kick user
        </button>

        <button
            :class="buttonClass('rd-bg mb-2 text-white')"
            @click="restartRoom"
            v-if="isHost"
        >
            Restart room
        </button>

        <button
            :class="buttonClass('bg-red-600 text-white')"
            @click="isHost ? closeRoom() : leaveRoom()"
            v-if="isSelf"
        >
            {{ isHost ? "Close" : "Leave" }} room
        </button>
    </Modal>
</template>

<script setup lang="ts">
import { PropType, ref, toRefs } from "vue";
import { RoomUser } from "../store/authStore";
import { api } from "../api";
import { useRouter } from "vue-router";
import Modal from "./Modal.vue";

const emit = defineEmits(["close", "restart"]);

const buttonClass = (c: string) => `py-1 px-8 rounded text-base ${c} w-full`;

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
        emit("close");
        window.location.href = "/";
    });
};
const kickUser = async () => {
    apiReq("/room/kick", { position: user.value.position }, () => {
        emit("close");
    });
};

const restartRoom = async () => {
    loading.value = true;
    await props.restartFn();
    emit("close");
    loading.value = false;
};
</script>
