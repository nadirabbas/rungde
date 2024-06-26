<template>
    <div class="audios"></div>
</template>

<script setup lang="ts">
import { Device } from "mediasoup-client";
import { io } from "socket.io-client";
import { onMounted, onUnmounted, ref, toRefs } from "vue";
import { useAuthStore } from "../store/authStore";
import { api } from "../api";
import { Consumer, Producer } from "mediasoup-client/lib/types";
import { Socket } from "socket.io-client";
import { useBus } from "../composables/useBus";
import { watchStreamAudioLevel } from "stream-audio-level";
import { useVoiceServer } from "../composables/useVoiceServer";

const bus = useBus();

const props = defineProps({
    roomId: {
        type: Number,
        required: true,
    },
    muteMap: {
        type: Object,
        required: true,
    },
});

const { roomId, muteMap } = toRefs(props);
const authStore = useAuthStore();
const tries = ref(0);
const socket = ref<Socket | null>();
const mediaStreamAudioWatchers = ref<Record<string, () => void>>({});
const muteTimeouts = ref<Record<string, NodeJS.Timeout>>({});

const consumers = ref<Consumer[]>([]);
const producer = ref<Producer>();

const init = async () => {
    tries.value++;
    try {
        const device = new Device();
        socket.value = useVoiceServer();

        const connected = async () => {
            socket.value?.on("error", (msg) => {
                window.alert(msg);
            });

            const res = await api.get("/room/voice-token");
            const voiceToken = res.data.token;

            // Join room
            socket.value?.emit("joinRoom", {
                roomId: roomId.value,
                userId: authStore.user?.id,
                token: voiceToken,
            });

            socket.value?.once(
                "room_joined",
                async ({
                    routerRtpCapabilities,
                    recvTransportOptions,
                    sendTransportOptions,
                }) => {
                    await device.load({ routerRtpCapabilities });

                    if (!device.canProduce("audio")) {
                        throw new Error("cannot produce audio");
                    }

                    const sendTr =
                        device.createSendTransport(sendTransportOptions);

                    const connTr = (tr, direction: "send" | "recv") => {
                        tr.on(
                            "connect",
                            async ({ dtlsParameters }, resolve, reject) => {
                                socket.value?.emit(
                                    "connect-mediasoup-transport",
                                    {
                                        token: voiceToken,
                                        roomId: roomId.value,
                                        dtlsParameters,
                                        userId: authStore.user?.id,
                                        direction,
                                    },
                                    (res) => {
                                        if (res.error) {
                                            reject(res.message);
                                        }
                                    }
                                );

                                socket.value?.once(
                                    `${direction}-transport-connected`,
                                    () => {
                                        resolve();
                                    }
                                );
                            }
                        );
                    };

                    // Produce audio
                    connTr(sendTr, "send");

                    sendTr.on(
                        "produce",
                        ({ kind, rtpParameters, appData }, resolve, reject) => {
                            socket.value?.emit(
                                "create_producer",
                                {
                                    roomId: roomId.value,
                                    userId: authStore.user?.id,
                                    token: voiceToken,
                                    transportId: sendTr.id,
                                    kind: "audio",
                                    rtpParameters,
                                    rtpCapabilities: device.rtpCapabilities,
                                    paused: false,
                                },
                                (res) => {
                                    if (res.error) {
                                        reject(res.message);
                                    }
                                }
                            );

                            socket.value?.once(
                                "producer_created",
                                async ({ producerId }) => {
                                    bus.emit("vc_connected", audioTrack);
                                    resolve(producerId);
                                }
                            );
                        }
                    );

                    const stream = await navigator.mediaDevices.getUserMedia({
                        audio: true,
                    });
                    const audioTrack = stream.getAudioTracks()[0];

                    audioTrack.enabled = false;
                    producer.value = await sendTr.produce({
                        track: audioTrack,
                    });

                    const recvTr =
                        device.createRecvTransport(recvTransportOptions);
                    connTr(recvTr, "recv");

                    socket.value?.emit("get_consumers", {
                        roomId: roomId.value,
                        userId: authStore.user?.id,
                        rtpCapabilities: device.rtpCapabilities,
                        token: voiceToken,
                    });

                    const playOutput = (
                        track: MediaStreamTrack,
                        userId: number
                    ) => {
                        const audio = new Audio();
                        audio.srcObject = new MediaStream([track]);
                        audio.muted = false;
                        audio.autoplay = true;
                        audio.play();

                        mediaStreamAudioWatchers.value[userId.toString()] =
                            watchStreamAudioLevel(
                                new MediaStream([track]),
                                (v) => {
                                    if (muteTimeouts.value[userId.toString()]) {
                                        return;
                                    }

                                    if (v > 120) {
                                        bus.emit("speaking", userId);
                                        muteTimeouts.value[userId.toString()] =
                                            setTimeout(() => {
                                                delete muteTimeouts.value[
                                                    userId.toString()
                                                ];
                                            }, 800);
                                    } else {
                                        bus.emit("quiet", userId);
                                    }
                                },
                                {
                                    minHz: 200,
                                    maxHz: 1000,
                                }
                            );
                    };

                    socket.value?.on(
                        "consumers_created",
                        async ({ consumers: remoteConsumers }) => {
                            for (const obj of remoteConsumers) {
                                const newConsumer = await recvTr.consume({
                                    ...obj.consumer.consumerParameters,
                                    appData: {
                                        userId: obj.userId,
                                    },
                                });
                                if (muteMap.value[obj.userId]) {
                                    newConsumer.pause();
                                }
                                consumers.value.push(newConsumer);
                                playOutput(newConsumer.track, obj.userId);
                            }
                        }
                    );

                    socket.value?.on(
                        "consumer_created",
                        async ({ consumer: remoteConsumer, userId }) => {
                            const consumer = await recvTr.consume({
                                ...remoteConsumer.consumerParameters,
                                appData: {
                                    userId,
                                },
                            });
                            consumers.value.push(consumer);
                            if (muteMap.value[userId]) {
                                consumer.pause();
                            }
                            playOutput(consumer.track, userId);
                        }
                    );

                    socket.value?.on(
                        "consumer_closed",
                        async ({ producerId, userId }) => {
                            consumers.value = consumers.value.filter(
                                (c) => c.producerId !== producerId
                            );

                            const clearWatcher =
                                mediaStreamAudioWatchers.value[
                                    userId.toString()
                                ];
                            if (clearWatcher) {
                                clearWatcher();
                            }
                        }
                    );
                }
            );
        };

        if (socket.value?.connected) {
            connected();
        }

        socket.value?.on("connect", async () => {
            connected();
        });

        socket.value?.on("disconnect", () => {
            socket.value?.removeAllListeners();
            init();
        });
    } catch (err) {
        if (tries.value < 10) {
            init();
        }
        console.error(err);
    }
};

onMounted(init);
onUnmounted(() => {
    Object.values(mediaStreamAudioWatchers.value).forEach((uw) => uw());
    socket.value?.removeAllListeners();
    socket.value?.disconnect();
});

const emit = defineEmits(["update:mute-map"]);

const muteUser = (userId: number) => {
    emit("update:mute-map", { ...muteMap.value, [userId]: true });
    consumers.value.forEach((c) => {
        if (c.appData.userId == userId) {
            c.pause();
        }
    });
};

const unmuteUser = (userId: number) => {
    emit("update:mute-map", { ...muteMap.value, [userId]: false });
    consumers.value.forEach((c) => {
        if (c.appData.userId == userId) {
            c.resume();
        }
    });
};

bus.on("mute-user", (uid: any) => muteUser(uid));
bus.on("unmute-user", (uid: any) => unmuteUser(uid));
</script>
