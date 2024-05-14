<template>
    <div
        id="communications"
        class="fixed right-48 top-5 flex items-center gap-2.5"
    >
        <div id="vc"></div>
        <Spectators
            :room="room"
            @update:mute-map="$emit('update:mute-map', $event)"
            :mute-map="muteMap"
            @update:mute-emoji-map="$emit('update:mute-emoji-map', $event)"
            :mute-emoji-map="muteEmojiMap"
            :channel="channel"
            :is-spectating="isSpectating"
            v-if="channel"
        />
        <div id="c"></div>
        <div id="em"></div>
    </div>
</template>

<script setup lang="ts">
import { PropType } from "vue";
import Spectators from "./Spectators.vue";
import { Channel } from "pusher-js";

const props = defineProps({
    room: null,
    isSpectating: Boolean,
    muteMap: {
        type: Object,
        required: true,
    },
    muteEmojiMap: {
        type: Object,
        required: true,
    },
    channel: {
        type: Object as PropType<Channel>,
        required: true,
    },
});
</script>
