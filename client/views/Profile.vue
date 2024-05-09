<template>
    <div class="relative">
        <router-link
            to="/"
            class="mb-3 rd-bg text-white text-sm rounded flex items-center gap-3 max-w-max p-1"
        >
            <ArrowLeftIcon class="w-4" />
        </router-link>

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
import { ArrowLeftIcon } from "heroicons-vue3/solid";

const route = useRoute();
const username = computed(() => route.params.username);
</script>
