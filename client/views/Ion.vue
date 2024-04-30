<template></template>

<script setup lang="ts">
import { Client, LocalStream, RemoteStream } from "ion-sdk-js";
import { IonSFUJSONRPCSignal } from "ion-sdk-js/lib/signal/json-rpc-impl";

const signal = new IonSFUJSONRPCSignal("ws://34.18.66.124:7000/ws");
const client = new Client(signal);

signal.onopen = async () => {
    await client.join("test session", "test uid");

    //@ts-ignore
    LocalStream.getUserMedia({
        audio: true,
        video: false,
    }).then((stream) => {
        console.log("publishing stream");
        client.publish(stream);
    });
};

client.ontrack = (track: MediaStreamTrack, stream: RemoteStream) => {};
</script>
