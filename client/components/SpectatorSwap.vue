<template>
    <div>
        <!-- Confirmation -->
        <div class="flex items-center p-3 justify-between min-w-[40vw]">
            <p class="text-lg">
                <template v-if="!isRequested">
                    {{
                        requestedFor
                            ? `Switch places with @${requestedFor.username}`
                            : `@${incomingRequestBy.username} wants to switch places.`
                    }}
                </template>
                <template v-else>
                    Waiting for @{{
                        (requestedFor || incomingRequestBy).username
                    }}
                    to accept...
                </template>
            </p>

            <div class="flex items-center gap-3">
                <button
                    :class="buttonClass('rd-bg')"
                    @click="swapPlaces"
                    v-if="!isRequested"
                >
                    <CheckIcon class="w-5" />
                </button>
                <button :class="buttonClass('bg-red-500')" @click="cancel">
                    <XIcon class="w-5" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { PropType, onMounted, onUnmounted, ref, toRefs } from "vue";
import { User, useAuthStore } from "../store/authStore";
import { Channel } from "pusher-js";
import { CheckIcon, XIcon } from "heroicons-vue3/solid";
import { api } from "../api";

const props = defineProps({
    requestedFor: {
        type: Object as PropType<User>,
        required: false,
    },
    incomingRequestBy: {
        type: Object as PropType<User>,
        required: false,
    },
    channel: {
        type: Object as PropType<Channel>,
        required: true,
    },
});

const isRequested = ref(false);
const { requestedFor, channel, incomingRequestBy } = toRefs(props);

const buttonClass = (c) => `${c} px-2 p-1 rounded text-base text-white`;
const loading = ref(false);
const emit = defineEmits(["close"]);

const authStore = useAuthStore();

const swapPlaces = () => {
    isRequested.value = true;
    if (incomingRequestBy?.value) {
        channel.value.trigger("client-swap-accept", {
            userId: incomingRequestBy?.value?.id,
            spectatorId: authStore.user?.id,
        });
        return;
    }

    channel.value.trigger("client-swap", {
        user: authStore.user,
        forId: requestedFor?.value?.id,
    });
};

const swapAccepted = async ({
    userId,
    spectatorId,
}: {
    userId: number;
    spectatorId: number;
}) => {
    loading.value = true;

    try {
        await api.post("/room/swap-places", {
            spectator_id: spectatorId,
        });
        channel.value.trigger("client-places-swapped", {
            involved: [userId, spectatorId],
        });
        emit("close");
    } catch (err) {
        console.error(err);
    }

    loading.value = false;
};

const cancel = () => {
    if (incomingRequestBy?.value) {
        channel.value.trigger("client-swap-deny", {
            user: authStore.user,
            forId: incomingRequestBy?.value?.id,
        });
    }

    emit("close");
};

onMounted(() => {
    channel.value.bind("client-swap-accept", swapAccepted);
});
onUnmounted(() => {
    channel.value.unbind("client-swap-accept");
});
</script>
