<template>
    <div
        :class="{
            'bg-white rounded': true,
            'border-[3px] border-green-600': high,
            'shadow-sm shadow-black': !highlighted,
            'highlighted-card': highlighted && !inactive,
        }"
    >
        <img
            :src="`/cards/${card}.svg`"
            :style="`min-width: ${width}vw; max-width: ${width}vw`"
            :class="{
                rounded: true,
            }"
        />

        <div
            class="absolute top-0 left-0 w-full h-full bg-opacity-40 rounded bg-black"
            v-if="inactive"
        ></div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    card: String,
    width: Number,
    high: Boolean,
    inactive: Boolean,
    highlighted: Boolean,
});
</script>

<style lang="scss">
.highlighted-card {
    box-shadow: 0 0 1px 2px #ffd700;
}
</style>
