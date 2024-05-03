<template>
    <div>
        <button
            :class="{
                'bg-white text-black border-[3px] flex items-center justify-center  rounded-full z-40 fixed lg:right-[185px] lg:top-5 left-5 lg:left-auto top-[87px] transition':
                    isSelf,
                'text-white': !isSelf,
                'text-red-600 opacity-40': muted && isSelf,
                hidden: !isSpeaking && !isSelf,
                'ring-8 ring-red-600': isSpeaking && isSelf && !muted,
                'border-white': !isSpeaking && isSelf,
                'w-8 h-8': !isSelf,
                'w-16 h-16 lg:w-8 lg:h-8': isSelf,
            }"
            @mousedown="unmute"
            @mouseup="mute"
            @touchstart="unmute"
            @touchcancel="mute"
            @touchmove="mute"
            @touchend="mute"
            v-if="connected"
        >
            <MicrophoneIcon
                :class="{
                    'w-4': !isSelf,
                    'w-8 lg:w-4': isSelf,
                }"
            />

            <TransitionFade>
                <span
                    class="top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 w-10 h-[3px] bg-red-500 absolute rotate-45"
                    v-if="muted && isSelf"
                ></span>
            </TransitionFade>
        </button>
    </div>
</template>

<script setup lang="ts">
import { MicrophoneIcon } from "heroicons-vue3/solid";
import { useBus } from "../composables/useBus";
import { onMounted, onUnmounted, ref, toRefs, watch } from "vue";
import { TransitionFade } from "@morev/vue-transitions";
import DecibelMeter from "decibel-meter";
const bus = useBus();

const props = defineProps({
    isSelf: Boolean,
    userId: {
        type: Number,
        required: true,
    },
});
const { userId, isSelf } = toRefs(props);

const connected = ref(false);
const muted = ref(true);
const audioTrack = ref<MediaStreamTrack>();

const muteTimer = ref();

const mute = () => {
    muted.value = true;
    audioTrack.value!.enabled = false;
};

const unmute = () => {
    muted.value = false;
    audioTrack.value!.enabled = true;
};

const toggleMute = (e: KeyboardEvent) => {
    if (!(e.key === "V" || e.key === "v") || e.repeat) return;

    if (muted.value) {
        unmute();
    } else {
        mute();
    }
};

const isSpeaking = ref(false);

bus.on("speaking", (uid) => {
    if (uid === userId.value) {
        isSpeaking.value = true;
    }
});

bus.on("quiet", (uid) => {
    if (uid === userId.value) {
        isSpeaking.value = false;
    }
});

bus.on("vc_connected", (myAudioTrack) => {
    connected.value = true;
    audioTrack.value = myAudioTrack as MediaStreamTrack;
});

const timeout = ref<NodeJS.Timeout>();
onMounted(() => {
    if (!isSelf.value) return;

    window.addEventListener("keydown", toggleMute);
    window.addEventListener("keyup", toggleMute);

    new DecibelMeter().listenTo(0, (dB, percent, value) => {
        if (percent > 70 && !muted.value) {
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

onUnmounted(() => {
    try {
        window.removeEventListener("keydown", toggleMute);
        window.removeEventListener("keyup", toggleMute);
    } catch (error) {}
});
</script>
