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

const bus = useBus();

const props = defineProps({
    roomId: {
        type: Number,
        required: true,
    },
});

const { roomId } = toRefs(props);
const authStore = useAuthStore();
const tries = ref(0);
const socket = ref<Socket | null>();
const mediaStreamAudioWatchers = ref<Record<string, () => void>>({});
const muteTimeouts = ref<Record<string, NodeJS.Timeout>>({});

const init = async () => {
    let consumers: Consumer[] = [];
    let producer: Producer;

    tries.value++;
    try {
        const device = new Device();
        socket.value = io(import.meta.env.VITE_VOICE_SERVER);
        socket.value?.on("connect", async () => {
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
                                    resolve(producerId);
                                }
                            );
                        }
                    );

                    const stream = await navigator.mediaDevices.getUserMedia({
                        audio: true,
                    });
                    const audioTrack = stream.getAudioTracks()[0];
                    producer = await sendTr.produce({
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
                                    if (v > 80) {
                                        audio!.muted = false;
                                        if (
                                            muteTimeouts.value[
                                                userId.toString()
                                            ]
                                        ) {
                                            clearTimeout(
                                                muteTimeouts.value[
                                                    userId.toString()
                                                ]
                                            );
                                        }
                                        muteTimeouts.value[userId.toString()] =
                                            setTimeout(() => {
                                                audio!.muted = true;
                                            }, 2000);
                                        bus.emit("speaking", userId);
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
                        async ({ consumers: remoteConsumers, userId }) => {
                            consumers = await Promise.all(
                                remoteConsumers.map(({ consumerParameters }) =>
                                    recvTr.consume(consumerParameters)
                                )
                            );
                            consumers.forEach((c) =>
                                playOutput(c.track, userId)
                            );
                        }
                    );

                    socket.value?.on(
                        "consumer_created",
                        async ({ consumer: remoteConsumer, userId }) => {
                            const consumer = await recvTr.consume(
                                remoteConsumer.consumerParameters
                            );
                            playOutput(consumer.track, userId);
                        }
                    );

                    socket.value?.on(
                        "consumer_closed",
                        async ({ producerId, userId }) => {
                            consumers = consumers.filter(
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
</script>
