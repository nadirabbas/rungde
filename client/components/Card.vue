<template>
    <div
        :class="{
            'aspect-[8/11] rounded-lg p-0 card-shadow acptn': true,
            'bg-[#ebeffa]': !highlighted,
            'golden-card': highlighted,
            'text-red-700': suite === 'h' || suite === 'd',
            'text-black': suite === 'c' || suite === 's',
            backside: isBack,
            'border-[3px] border-green-600': high,
        }"
        :style="`width: 8.2vw`"
    >
        <template v-if="!isBack">
            <span
                class="text-[170%] font-black absolute left-2 top-2 leading-none"
                >{{ rank }}</span
            >

            <img
                :src="`/cards/${suite}.svg`"
                class="w-[40%] absolute bottom-4 left-2"
            />

            <div
                class="absolute top-0 left-0 w-full h-full bg-opacity-40 rounded-lg bg-black"
                v-if="inactive"
            ></div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed, toRefs } from "vue";
import { cardNum } from "../utils/gameHelper";

const props = defineProps({
    card: {
        type: String,
        required: true,
    },
    width: Number,
    high: Boolean,
    inactive: Boolean,
    highlighted: Boolean,
    hidden: Boolean,
});

const { card, hidden } = toRefs(props);

const isBack = computed(() => card.value === "back" || hidden.value);
const suite = computed(() => card.value[0]);
const rank = computed(() => {
    const num = cardNum(card.value);
    return (
        {
            14: "A",
            13: "K",
            12: "Q",
            11: "J",
        }[num] || num
    );
});
</script>

<style lang="scss">
.card-shadow {
    box-shadow: 0 0 1.5px 2.5px rgba(0, 0, 0, 0.3);
}

.backside {
    background-color: #f6f4f9;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 52 52'%3E%3Cpath fill='%23022cff' fill-opacity='0.4' d='M0 17.83V0h17.83a3 3 0 0 1-5.66 2H5.9A5 5 0 0 1 2 5.9v6.27a3 3 0 0 1-2 5.66zm0 18.34a3 3 0 0 1 2 5.66v6.27A5 5 0 0 1 5.9 52h6.27a3 3 0 0 1 5.66 0H0V36.17zM36.17 52a3 3 0 0 1 5.66 0h6.27a5 5 0 0 1 3.9-3.9v-6.27a3 3 0 0 1 0-5.66V52H36.17zM0 31.93v-9.78a5 5 0 0 1 3.8.72l4.43-4.43a3 3 0 1 1 1.42 1.41L5.2 24.28a5 5 0 0 1 0 5.52l4.44 4.43a3 3 0 1 1-1.42 1.42L3.8 31.2a5 5 0 0 1-3.8.72zm52-14.1a3 3 0 0 1 0-5.66V5.9A5 5 0 0 1 48.1 2h-6.27a3 3 0 0 1-5.66-2H52v17.83zm0 14.1a4.97 4.97 0 0 1-1.72-.72l-4.43 4.44a3 3 0 1 1-1.41-1.42l4.43-4.43a5 5 0 0 1 0-5.52l-4.43-4.43a3 3 0 1 1 1.41-1.41l4.43 4.43c.53-.35 1.12-.6 1.72-.72v9.78zM22.15 0h9.78a5 5 0 0 1-.72 3.8l4.44 4.43a3 3 0 1 1-1.42 1.42L29.8 5.2a5 5 0 0 1-5.52 0l-4.43 4.44a3 3 0 1 1-1.41-1.42l4.43-4.43a5 5 0 0 1-.72-3.8zm0 52c.13-.6.37-1.19.72-1.72l-4.43-4.43a3 3 0 1 1 1.41-1.41l4.43 4.43a5 5 0 0 1 5.52 0l4.43-4.43a3 3 0 1 1 1.42 1.41l-4.44 4.43c.36.53.6 1.12.72 1.72h-9.78zm9.75-24a5 5 0 0 1-3.9 3.9v6.27a3 3 0 1 1-2 0V31.9a5 5 0 0 1-3.9-3.9h-6.27a3 3 0 1 1 0-2h6.27a5 5 0 0 1 3.9-3.9v-6.27a3 3 0 1 1 2 0v6.27a5 5 0 0 1 3.9 3.9h6.27a3 3 0 1 1 0 2H31.9z'%3E%3C/path%3E%3C/svg%3E");
}
</style>
