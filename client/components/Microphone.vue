<template>
    <div>
        <button
            :class="{
                ' flex items-center justify-center w-8 h-8 rounded-full z-40 fixed right-[185px] top-5':
                    isSelf,
                'text-white': !isSelf,
                'text-red-500': muted && isSelf,
                hidden: !isSpeaking && !isSelf,
                'bg-red-500 text-white': isSpeaking && isSelf,
                'bg-white text-black': !isSpeaking && isSelf,
            }"
            @click="toggleMute"
            v-if="stream || !isSelf"
        >
            <MicrophoneIcon class="w-4" />

            <TransitionFade>
                <span
                    class="top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 w-6 h-[2px] bg-red-500 absolute rotate-45"
                    v-if="muted && isSelf"
                ></span>
            </TransitionFade>
        </button>
    </div>
</template>

<script setup lang="ts">
import { MicrophoneIcon } from "heroicons-vue3/solid";
import { useBus } from "../composables/useBus";
import { onMounted, ref, toRefs } from "vue";
import { TransitionFade } from "@morev/vue-transitions";
import { LocalStream } from "ion-sdk-js";
import DecibelMeter from "decibel-meter";
const bus = useBus();

const props = defineProps({
    isSelf: Boolean,
    userId: {
        type: Number,
        required: true,
    },
    streamId: {
        type: String,
        required: true,
    },
});
const { streamId, isSelf } = toRefs(props);

const muted = ref(true);
const stream = ref<LocalStream>();

bus.on("mystream", (s: any) => {
    if (isSelf.value) {
        stream.value = s;
    }
});

const toggleMute = () => {
    bus.emit(muted.value ? "unmute" : "mute", { isMine: isSelf.value });
    muted.value = !muted.value;
};

const isSpeaking = ref(false);

bus.on("speaking", (sid) => {
    if (sid == streamId.value) {
        isSpeaking.value = true;
    }
});

bus.on("quiet", (sid) => {
    if (sid == streamId.value) {
        isSpeaking.value = false;
    }
});

const timeout = ref<NodeJS.Timeout>();
onMounted(() => {
    if (!isSelf.value) return;

    new DecibelMeter().listenTo(0, (dB, percent, value) => {
        if (percent > 30 && !muted.value) {
            isSpeaking.value = true;
            if (timeout.value) {
                clearTimeout(timeout.value);
            }
            timeout.value = setTimeout(() => {
                isSpeaking.value = false;
            }, 300);
        }
    });
});
</script>
