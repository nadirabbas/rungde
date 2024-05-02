<template>
    <div class="audios"></div>
</template>

<script setup lang="ts">
import { Device } from "mediasoup-client";
import { io } from "socket.io-client";
import { onMounted, ref } from "vue";
import { useAuthStore } from "../store/authStore";
import { api } from "../api";
import { Consumer, Producer, Transport } from "mediasoup-client/lib/types";
import { useGeneralStore } from "../store/generalStore";

const generalStore = useGeneralStore();

const authStore = useAuthStore();

const tries = ref(0);

const init = async () => {
    let consumers: Consumer[] = [];
    let producer: Producer;

    tries.value++;
    try {
        const device = new Device();
        const socket = io(import.meta.env.VITE_VOICE_SERVER);
        socket.on("connect", async () => {
            socket.on("error", (msg) => {
                window.alert(msg);
            });

            const res = await api.get("/room/voice-token");
            const voiceToken = res.data.token;

            // Join room
            socket.emit("joinRoom", {
                roomId: generalStore.tempRoomId,
                userId: authStore.user?.id,
                token: voiceToken,
            });

            socket.once(
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
                                socket.emit(
                                    "connect-mediasoup-transport",
                                    {
                                        token: voiceToken,
                                        roomId: generalStore.tempRoomId,
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

                                socket.once(
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
                            socket.emit(
                                "create_producer",
                                {
                                    roomId: generalStore.tempRoomId,
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

                            socket.once(
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

                    socket.emit("get_consumers", {
                        roomId: generalStore.tempRoomId,
                        userId: authStore.user?.id,
                        rtpCapabilities: device.rtpCapabilities,
                        token: voiceToken,
                    });

                    const playOutput = (track: MediaStreamTrack) => {
                        const audio = new Audio();
                        audio.srcObject = new MediaStream([track]);
                        audio.muted = false;
                        audio.autoplay = true;
                        audio.play();
                    };

                    socket.on(
                        "consumers_created",
                        async ({ consumers: remoteConsumers }) => {
                            consumers = await Promise.all(
                                remoteConsumers.map(({ consumerParameters }) =>
                                    recvTr.consume(consumerParameters)
                                )
                            );
                            consumers.forEach((c) => playOutput(c.track));
                        }
                    );

                    socket.on(
                        "consumer_created",
                        async ({ consumer: remoteConsumer }) => {
                            const consumer = await recvTr.consume(
                                remoteConsumer.consumerParameters
                            );
                            playOutput(consumer.track);
                        }
                    );

                    socket.on("consumer_closed", async ({ producerId }) => {
                        consumers = consumers.filter(
                            (c) => c.producerId !== producerId
                        );
                    });
                }
            );
        });

        socket.on("disconnect", () => {
            socket.removeAllListeners();
            init();
        });
    } catch (err) {
        console.error(err);
    }
};

onMounted(() => {
    window.addEventListener("keydown", (e) => {
        if (e.key === "`") {
            init();
        }
    });
});
</script>
