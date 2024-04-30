<template>
    <span></span>
</template>

<script setup lang="ts">
import { Client, LocalStream, RemoteStream } from "ion-sdk-js";
import { IonSFUJSONRPCSignal } from "ion-sdk-js/lib/signal/json-rpc-impl";
import { PropType, onMounted, onUnmounted, ref, toRefs, watch } from "vue";
import { RoomUser } from "../store/authStore";
import { MicrophoneIcon } from "heroicons-vue3/solid";
import { useBus } from "../composables/useBus";
import { api } from "../api";
import { watchStreamAudioLevel } from "stream-audio-level";
import { useGeneralStore } from "../store/generalStore";
import { storeToRefs } from "pinia";
const bus = useBus();
const generalStore = useGeneralStore();

const props = defineProps({
    participants: {
        type: Array as PropType<RoomUser[]>,
        required: true,
    },
    roomId: {
        type: Number,
        required: true,
    },
    userId: {
        type: Number,
        required: true,
    },
});

const { roomId, userId } = toRefs(props);

const signal = ref<IonSFUJSONRPCSignal>();
const client = ref<Client>();

const myStream = ref<LocalStream>();

const remoteStreams = ref<Record<string, RemoteStream>>({});
const mediaStreamAudioWatchers = ref<Record<string, () => void>>({});
const muteTimeouts = ref<Record<string, NodeJS.Timeout>>({});

const userAudioElements = ref<Record<string, HTMLAudioElement>>({});

const updateUserStreamId = (sid: string) => {
    return api.put("/me/stream", {
        stream_id: sid,
    });
};

const setMediaStream = (
    isMine: boolean,
    stream: LocalStream | RemoteStream
) => {
    if (isMine) {
        bus.emit("mystream", stream);
        myStream.value = stream as LocalStream;
        updateUserStreamId(stream.id);
    } else {
        remoteStreams.value[stream.id] = stream as RemoteStream;
    }

    mediaStreamAudioWatchers.value[stream.id] = watchStreamAudioLevel(
        stream,
        (v) => {
            const audio = userAudioElements.value[stream.id];
            if (v > 80) {
                audio!.muted = false;
                if (muteTimeouts.value[stream.id]) {
                    clearTimeout(muteTimeouts.value[stream.id]);
                }
                muteTimeouts.value[stream.id] = setTimeout(() => {
                    audio!.muted = true;
                    console.log("muting again");
                }, 2000);
                bus.emit("speaking", stream.id);
            } else {
                bus.emit("quiet", stream.id);
            }
        },
        {
            minHz: 200,
            maxHz: 1000,
        }
    );
};

const startAudioStream = () => {
    // @ts-ignore
    LocalStream.getUserMedia({
        audio: true,
        video: false,
        resolution: "vga",
        codec: "vp8",
    }).then((m) => {
        m.mute("audio");
        setMediaStream(true, m);
        client.value?.publish(m);
    });
};

const { hasUserInteracted } = storeToRefs(generalStore);
const init = () => {
    if (!hasUserInteracted.value) return;
    if (signal.value && client.value) return;

    const config = {
        iceServers: [
            {
                urls: "stun:stun.l.google.com:19302",
            },
        ],
    };
    signal.value = new IonSFUJSONRPCSignal("ws://34.18.66.124:7000/ws");
    // @ts-ignore
    client.value = new Client(signal.value, config);

    signal.value.onopen = async () => {
        await client.value?.join(
            roomId.value.toString(),
            userId.value.toString()
        );

        startAudioStream();
    };

    client.value.ontrack = (track, stream) => {
        setMediaStream(false, stream);

        track.onunmute = () => {
            let audioElement = userAudioElements.value[stream.id];
            if (!audioElement) {
                audioElement = new Audio();
                audioElement.autoplay = true;
                audioElement.muted = false;
                userAudioElements.value[stream.id] = audioElement;
            }

            audioElement.srcObject = new MediaStream([track]);
            audioElement.play();
        };

        stream.onremovetrack = () => {
            delete userAudioElements.value[stream.id];

            const unwatch = mediaStreamAudioWatchers.value[stream.id];
            if (unwatch) {
                console.log("removing " + stream.id);
                unwatch();
                delete mediaStreamAudioWatchers.value[stream.id];
            }
            userAudioElements.value[stream.id]?.remove();
        };
    };
};

const toggleMute =
    (type) =>
    ({ isMine, sid }: any) => {
        const stream = isMine ? myStream.value : remoteStreams.value[sid];
        if (!stream) return;

        if (type === "mute") {
            stream.mute("audio");
        } else {
            stream.unmute("audio");
        }
    };

bus.on("mute", toggleMute("mute"));
bus.on("unmute", toggleMute("unmute"));

onMounted(init);
watch(hasUserInteracted, init);

// Clean up
onUnmounted(() => {
    myStream.value?.unpublish();
    client.value?.close();
    signal.value?.close();
    Object.values(mediaStreamAudioWatchers.value).forEach((unwatch) =>
        unwatch()
    );
    Object.values(userAudioElements.value).forEach((audioElement) =>
        audioElement.remove()
    );
});
</script>
