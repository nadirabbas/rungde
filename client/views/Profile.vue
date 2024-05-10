<template>
    <div class="relative">
        <BackButton class="mb-3" />

        <ProfileCard
            :username="username"
            class="py-1 relative min-w-[35vw] lg:min-w-[300px] max-w-full"
        />
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";
import ProfileCard from "../components/ProfileCard.vue";
import BackButton from "../components/BackButton.vue";

const route = useRoute();
const username = computed(() => route.params.username);
</script>
