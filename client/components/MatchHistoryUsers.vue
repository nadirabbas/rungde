<template>
    <div
        :class="{
            'flex flex-col flex-1': true,
            'items-end': !isWinner,
        }"
    >
        <h1
            :class="{
                'font-bold text-white': true,
                'text-lg': authStore.user.id === u.id,
            }"
            v-for="u in users"
            :key="u.id"
        >
            {{ u.username }}
        </h1>
    </div>
</template>

<script setup lang="ts">
import { PropType, toRefs } from "vue";
import { HistoryUser } from "./MatchHistory.vue";
import { useAuthStore } from "../store/authStore";
const authStore = useAuthStore();

const props = defineProps({
    users: {
        type: Array as PropType<HistoryUser[]>,
        required: true,
    },
    isWinner: Boolean,
});
</script>
