<template>
    <div class="min-w-[60vw] max-h-[60vh] overflow-y-auto pr-2 relative">
        <MountedTeleport to="#back">
            <BackButton class="top-0 sticky" light />
        </MountedTeleport>

        <div>
            <div
                v-for="match in history"
                :key="match.id"
                class="rd-mhbg py-2 px-2 mb-2 rounded"
            >
                <div class="flex items-stretch">
                    <MatchHistoryUsers
                        is-winner
                        :users="[match.winner_1, match.winner_2]"
                    />
                    <div class="flex-1 flex justify-center items-center">
                        <p
                            :class="{
                                ' font-medium kal w-full text-center': true,
                                'text-white text-lg':
                                    !match.is_court && !match.is_goon_court,
                                'court-text':
                                    match.is_court || match.is_goon_court,
                            }"
                        >
                            {{ score(match) }}
                        </p>
                    </div>

                    <MatchHistoryUsers
                        :users="[match.loser_1, match.loser_2]"
                    />
                </div>
            </div>
        </div>

        <div class="w-full flex justify-center py-10" v-show="loading">
            <Spinner />
        </div>
    </div>
</template>

<script setup lang="ts">
import { PropType, onMounted, ref, toRefs, watch } from "vue";
import Spinner from "./Spinner.vue";
import MatchHistoryUsers from "./MatchHistoryUsers.vue";
import BackButton from "./BackButton.vue";
import MountedTeleport from "./MountedTeleport.vue";
import { useAuthStore } from "../store/authStore";

const props = defineProps({
    fetchHistory: {
        type: Function as PropType<
            (pagination?: { page?: number; only_self?: number }) => Promise<any>
        >,
        required: true,
    },
    showOnlySelf: Boolean,
});

const { fetchHistory: fetchHistoryFn, showOnlySelf } = toRefs(props);

const loading = ref(false);
const totalMatches = ref(0);
const history = ref<History[]>([]);

const page = ref(1);

const fetchHistory = async () => {
    loading.value = true;

    try {
        const res = await fetchHistoryFn.value({
            page: page.value,
            only_self: showOnlySelf.value ? 1 : 0,
        });
        history.value.push(...res.data);
        totalMatches.value = res.total;
    } catch (error) {
        console.error(error);
    }

    loading.value = false;
};

onMounted(fetchHistory);
watch(showOnlySelf, () => {
    history.value = [];
    fetchHistory();
});
</script>

<script lang="ts">
export interface HistoryUser {
    id: number;
    username: string;
}

export interface History {
    id: number;
    winner_1: HistoryUser;
    winner_2: HistoryUser;
    loser_1: HistoryUser;
    loser_2: HistoryUser;
    winner_score: number;
    loser_score: number;
    is_court: boolean;
    is_goon_court: boolean;
    created_at: string;
}

const score = (match: History) => {
    if (match.is_court) {
        return "Court";
    } else if (match.is_goon_court) {
        return "Goon Court";
    } else {
        return `${match.winner_score} - ${match.loser_score}`;
    }
};
</script>

<style lang="scss">
.rd-mhbg {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cg fill='%23022c50' fill-opacity='0.07'%3E%3Cpolygon fill-rule='evenodd' points='8 4 12 6 8 8 6 12 4 8 0 6 4 4 6 0 8 4'/%3E%3C/g%3E%3C/svg%3E"),
        linear-gradient(to right, #00a70b 40%, #a70000c3 60%);
}

.court-text {
    @apply text-xl;
    color: var(--color-yellow);
    text-shadow: 0 0 10px var(--color-yellow);
}
</style>
