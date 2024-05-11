<template>
    <div>
        <div class="max-h-4">
            <MountedTeleport to="#communications" :disabled="!isSelf">
                <UseMousePressed
                    v-if="connected && !hidden"
                    v-slot="{ pressed }"
                >
                    <div>
                        <PropWatcher
                            :watch="pressed"
                            @change="$event ? unmute() : mute()"
                        />
                        <button
                            :class="{
                                'bg-white text-black border-[3px] flex items-center justify-center  rounded-full z-40 fixed lg:relative lg:right-auto lg:top-auto left-5 lg:left-auto top-[20.5vh] transition':
                                    isSelf,
                                'text-white': !isSelf,
                                'text-black opacity-30': muted && isSelf,
                                'ring-8 lg:ring-4 ring-red-600':
                                    isSpeaking && isSelf && !muted,
                                'border-white': !isSpeaking && isSelf,
                                'w-16 h-16 lg:w-auto lg:h-8 lg:px-3': isSelf,
                                hidden: !isSpeaking && !isSelf,
                            }"
                        >
                            <div class="relative">
                                <MicrophoneIcon
                                    :class="{
                                        'w-4': !isSelf,
                                        'w-8 lg:w-4': isSelf,
                                    }"
                                />

                                <TransitionFade>
                                    <span
                                        class="top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 w-10 lg:w-5 lg:h-[2px] h-[3px] bg-black absolute rotate-45"
                                        v-if="muted && isSelf"
                                    ></span>
                                </TransitionFade>
                            </div>

                            <span
                                class="hidden lg:block text-sm ml-2 font-bold"
                                v-if="isSelf"
                                >{{ muted ? "Press V" : "Speak" }}</span
                            >
                        </button>
                    </div>
                </UseMousePressed>
            </MountedTeleport>
        </div>
    </div>
</template>

<script setup lang="ts">
import { MicrophoneIcon } from "heroicons-vue3/solid";
import { useBus } from "../composables/useBus";
import {
    computed,
    onMounted,
    onUnmounted,
    ref,
    toRefs,
    watch,
    watchEffect,
} from "vue";
import { TransitionFade } from "@morev/vue-transitions";
import DecibelMeter from "decibel-meter";
import MountedTeleport from "./MountedTeleport.vue";
import { useMagicKeys } from "@vueuse/core";
import { UseMousePressed } from "@vueuse/components";
import PropWatcher from "./PropWatcher.vue";

const bus = useBus();

const props = defineProps({
    isSelf: Boolean,
    userId: {
        type: Number,
        required: true,
    },
    hideForOthers: Boolean,
});
const { userId, isSelf, hideForOthers } = toRefs(props);

const hidden = computed(() => {
    if (hideForOthers.value && !isSelf.value) {
        return true;
    }

    return false;
});

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
    new DecibelMeter().listenTo(0, (dB, percent, value) => {
        if (percent > 50 && !muted.value) {
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

const { v } = useMagicKeys();
watch(v, () => {
    if (v.value) {
        unmute();
    } else {
        mute();
    }
});
</script>
