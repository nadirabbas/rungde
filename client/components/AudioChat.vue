<template>
    <span></span>
</template>

<script setup lang="ts">
import { Client, LocalStream, RemoteStream } from "ion-sdk-js";
import { IonSFUJSONRPCSignal } from "ion-sdk-js/lib/signal/json-rpc-impl";
import { PropType, onMounted, onUnmounted, ref, toRefs, watch } from "vue";
import { RoomUser } from "../store/authStore";
import { useBus } from "../composables/useBus";
import { api } from "../api";
import { watchStreamAudioLevel } from "stream-audio-level";
const bus = useBus();

const emit = defineEmits(["reinit"]);
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

const createAudioElementForStream = (stream: MediaStream, muted = false) => {
    const audioElement = new Audio();
    audioElement.autoplay = true;
    audioElement.muted = false;
    userAudioElements.value[stream.id] = audioElement;
    return audioElement;
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

    if (isMine) return;
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
    })
        .then((m) => {
            m.mute("audio");
            setMediaStream(true, m);
            client.value?.publish(m);

            console.log("publish local video", m);
            console.log("local client", client.value);
        })
        .catch((err) => {
            console.error("media devices error", err);
            emit("reinit");
        });
};

const keepAliveInterval = ref<NodeJS.Timeout>();

const init = () => {
    if (signal.value && client.value) return;

    const config = {
        iceServers: [
            {
                urls: "stun:stun.l.google.com:19302",
            },
        ],
    };
    signal.value = new IonSFUJSONRPCSignal("wss://rungde.lol:8443/ws");
    // @ts-ignore
    client.value = new Client(signal.value, config);

    signal.value.onopen = async () => {
        await client.value?.join(
            roomId.value.toString(),
            userId.value.toString()
        );

        keepAliveInterval.value = setInterval(() => {
            client.value?.join(
                roomId.value.toString(),
                userId.value.toString()
            );
        }, 1000 * 30);

        startAudioStream();
    };

    client.value.ontrack = (track, stream) => {
        setMediaStream(false, stream);

        console.log("new track", track);
        console.log("new stream", stream);

        let audioElement = userAudioElements.value[stream.id];
        if (!audioElement) {
            audioElement = createAudioElementForStream(stream);
            userAudioElements.value[stream.id] = audioElement;
        }

        audioElement.srcObject = new MediaStream([track]);
        audioElement.play();

        stream.onremovetrack = () => {
            console.log("track removed", track);
            console.log("stream removed", stream);

            delete userAudioElements.value[stream.id];

            const unwatch = mediaStreamAudioWatchers.value[stream.id];
            if (unwatch) {
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

// Clean up
onUnmounted(() => {
    if (keepAliveInterval.value) clearInterval(keepAliveInterval.value);

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
