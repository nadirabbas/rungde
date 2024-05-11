<template>
    <div>
        <MatchHistory
            :fetch-history="fetchHistory"
            :show-only-self="showOnlySelf"
        />
        <MountedTeleport to="#title"> Global Match History </MountedTeleport>
        <MountedTeleport to="#filters">
            <Checkbox v-model="showOnlySelf" label="Show My games" />
        </MountedTeleport>
    </div>
</template>

<script setup lang="ts">
import { api } from "../api";
import MatchHistory from "../components/MatchHistory.vue";
import MountedTeleport from "../components/MountedTeleport.vue";
import Checkbox from "../components/Checkbox.vue";
import { ref } from "vue";

const showOnlySelf = ref(false);

const fetchHistory = async (data) =>
    api.get("/match-history", { params: data }).then((res) => res.data);
</script>
