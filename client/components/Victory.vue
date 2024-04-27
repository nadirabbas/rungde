<template>
    <div
        class="fixed w-full h-full justify-center top-0 left-0 z-50 backdrop-blur bg-black bg-opacity-90 srd-bg-pattern flex flex-col items-center pt-20"
    >
        <h1
            :class="{
                'kal text-8xl -mt-10': true,
                'victory-glow text-yellow': victory,
                'defeat-glow text-red-100': defeat,
            }"
        >
            {{ text }}
        </h1>

        <div class="mt-16 w-52 max-w-full flex flex-col gap-3">
            <template v-if="!loading">
                <button
                    :class="buttonClass('rd-bg text-white')"
                    @click="isHost ? restartRoom() : leaveRoom()"
                >
                    {{ isHost ? "Start new game" : "Leave room" }}
                </button>
                <button
                    :class="buttonClass('bg-red-600 text-white')"
                    @click="closeRoom()"
                    v-if="isHost"
                >
                    Close room
                </button>
            </template>

            <p class="py-3 text-xl text-white font-bold text-center" v-else>
                Please wait...
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, toRefs } from "vue";
import { buttonClass } from "./GameMenu.vue";
import { api } from "../api";

const emit = defineEmits(["close"]);

const props = defineProps({
    victory: Boolean,
    defeat: Boolean,
    goonCourt: Boolean,
    court: Boolean,
    isHost: Boolean,
    restartFn: {
        type: Function,
        required: true,
    },
});

const refs = toRefs(props);

const text = computed(() => {
    if (refs.goonCourt.value !== null) {
        return "Goon Court!";
    } else if (refs.court.value !== null) {
        return "Court!";
    } else if (refs.victory.value) {
        return "Victory!";
    } else {
        return "Defeat!";
    }
});

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
const restartRoom = async () => {
    loading.value = true;
    await props.restartFn();
    loading.value = false;
};
</script>

<style lang="scss">
.victory-glow {
    animation: victory-glow 1.3s ease-in-out infinite alternate;
}

@keyframes victory-glow {
    0% {
        text-shadow: 0 0 5px var(--color-yellow);
    }

    100% {
        text-shadow: 0 0 20px var(--color-yellow);
    }
}

.defeat-glow {
    animation: defeat-glow 1.3s ease-in-out infinite alternate;
}

@keyframes defeat-glow {
    0% {
        text-shadow: 0 0 20px rgb(220 38 38);
    }

    100% {
        text-shadow: 0 0 40px rgb(220 38 38);
    }
}
</style>
