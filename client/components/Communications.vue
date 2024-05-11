<template>
    <div
        id="communications"
        class="fixed right-36 top-5 flex items-center gap-2.5"
    >
        <div id="vc"></div>
        <Spectators :room="room" />
        <div id="c"></div>
        <div id="em"></div>
    </div>
</template>

<script setup lang="ts">
import Spectators from "./Spectators.vue";

const props = defineProps({
    room: null,
});
</script>
