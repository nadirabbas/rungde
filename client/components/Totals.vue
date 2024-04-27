<template>
    <Modal
        :model-value="modelValue"
        @close="$emit('close')"
        title="Total score"
    >
        <div
            class="flex items-center justify-between text-lg mb-4 w-full"
            v-for="(t, i) in totals"
            :key="i"
        >
            <p class="text-base mr-6">{{ t.name }}</p>

            <div class="flex items-center gap-4">
                <span :class="scoreSpan('bg-green-600 rounded')">{{
                    t.our
                }}</span>
                <span class="scale-x-150">v</span>
                <span :class="scoreSpan('bg-red-600 rounded')">{{
                    t.their
                }}</span>
            </div>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
import { scoreSpan } from "../views/Game.vue";
import Modal from "./Modal.vue";
const props = defineProps({
    ourWins: Number,
    theirWins: Number,
    ourCourts: Number,
    theirCourts: Number,
    ourGoonCourts: Number,
    theirGoonCourts: Number,
    modelValue: Boolean,
});

const refs = toRefs(props);

const totals = [
    {
        name: "Courts",
        our: refs.ourCourts?.value,
        their: refs.theirCourts?.value,
    },
    {
        name: "Goon courts",
        our: refs.ourGoonCourts?.value,
        their: refs.theirGoonCourts?.value,
    },
    { name: "Overall", our: refs.ourWins?.value, their: refs.theirWins?.value },
];
</script>
