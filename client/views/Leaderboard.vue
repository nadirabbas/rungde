<template>
    <div class="min-w-[60vw] max-h-[50vh] overflow-y-auto">
        <MountedTeleport to="#title">Leaderboard</MountedTeleport>
        <MountedTeleport to="#filters">
            <div class="flex flex-col">
                <span class="text-white text-xs mb-0.5"> Ranked by </span>
                <select
                    v-model="sortBy"
                    class="focus:outline-none p-0.5 bg-white border-none text-sm rounded"
                >
                    <option
                        v-for="opt in sortByOptions"
                        :key="opt"
                        :value="opt"
                    >
                        {{ capital(opt) }}
                    </option>
                </select>
            </div>
        </MountedTeleport>

        <div class="flex flex-col gap-2" v-show="!loading">
            <div
                v-for="(u, i) in users"
                :key="u.id"
                class="w-full flex items-center justify-between rd-bg rounded p-2"
            >
                <div
                    class="text-white rounded-full ml-2 mr-4 font-bold text-lg"
                >
                    {{ i + 1 }}
                </div>

                <div class="flex items-center gap-2">
                    <Avatar :avatar="u.avatar" />

                    <span class="text-base text-white">@{{ u.username }}</span>
                </div>

                <div class="flex-1 text-center text-white text-lg font-medium">
                    {{ capital(sortBy) }}: {{ u[sortBy] }}
                </div>
            </div>
        </div>

        <div class="w-full flex justify-center py-10" v-show="loading">
            <Spinner />
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from "vue";
import Spinner from "../components/Spinner.vue";
import MountedTeleport from "../components/MountedTeleport.vue";
import { capital } from "case";
import { api } from "../api";
import { UserIcon } from "heroicons-vue3/solid";
import Avatar from "../components/Avatar.vue";

enum SortBy {
    GoonCourts = "goon_courts",
    Courts = "courts",
    Tricks = "tricks",
    GamesWon = "games_won",
    GamesPlayed = "games_played",
}

const loading = ref(false);
const sortBy = ref<SortBy>(SortBy.GoonCourts);
const users = ref([]);

const sortByOptions = Object.values(SortBy);

const getTopUsers = async () => {
    loading.value = true;

    try {
        const res = await api.get("/leaderboard", {
            params: {
                sort_by: sortBy.value,
            },
        });
        users.value = res.data.data.map((u) => ({
            ...u,
            tricks: u.sirs,
        }));
    } catch (error) {
        console.error(error);
    }

    loading.value = false;
};

onMounted(getTopUsers);
watch(sortBy, getTopUsers);
</script>
