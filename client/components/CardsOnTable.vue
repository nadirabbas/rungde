<template>
    <div
        class="fixed left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 tablecards"
    >
        <Card
            v-for="{ card, position } in availableCards"
            :key="position"
            :card="card"
            :width="7"
            :high="highCard === card"
            :class="{
                absolute: true,
                'bottom-3': position === teammatePosition,
                'rotate-90 translate-x-[140%] -translate-y-1/2':
                    position === nextPosition,
                'rotate-90 -translate-x-[140%] -translate-y-1/2':
                    position === previousPosition,
            }"
            :highlighted="card[0] === rung"
        />
    </div>
</template>

<script setup lang="ts">
import { PropType, computed, toRefs } from "vue";
import { RoomUser } from "../store/authStore";

import Card from "./Card.vue";
import {
    getHighestCard,
    getNextPosition,
    getPreviousPosition,
    getTeamMatePosition,
} from "../utils/gameHelper";

const props = defineProps({
    cards: {
        type: Object as PropType<{
            1: string;
            2: string;
            3: string;
            4: string;
        }>,
        required: true,
    },
    me: {
        type: Object as PropType<RoomUser>,
        required: true,
    },
    rung: String,
    turnRung: String,
    sirs: Number,
});

const { cards, me, rung, turnRung } = toRefs(props);

const availableCards = computed(() => {
    return Object.keys(cards.value)
        .map((position) => {
            const card = cards.value[position];
            if (!card) return null;

            return { position: parseInt(position), card };
        })
        .filter(Boolean);
});

const highCard = computed(() =>
    // @ts-ignore
    getHighestCard(
        availableCards.value.map((i) => i?.card),
        turnRung?.value,
        rung?.value
    )
);
const teammatePosition = computed(() => getTeamMatePosition(me.value.position));
const nextPosition = computed(() => getNextPosition(me.value.position));
const previousPosition = computed(() => getPreviousPosition(me.value.position));
</script>
