<template>
    <div>
        <MatchHistory :fetch-history="fetchHistory" />

        <MountedTeleport to="#title"> Global Match History </MountedTeleport>
    </div>
</template>

<script setup lang="ts">
import { api } from "../api";
import MatchHistory from "../components/MatchHistory.vue";
import MountedTeleport from "../components/MountedTeleport.vue";

const fetchHistory = async () =>
    api.get("/match-history").then((res) => res.data);
</script>
