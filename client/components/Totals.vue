<template>
    <Modal
        :model-value="modelValue"
        @close="$emit('close')"
        title="Total score"
    >
        <div class="flex items-center justify-center gap-4 text-lg">
            <span :class="scoreSpan('bg-green-600 rounded')">{{
                ourWins
            }}</span>
            <span>v</span>
            <span :class="scoreSpan('bg-red-600 rounded')">{{
                theirWins
            }}</span>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import { scoreSpan } from "../views/Game.vue";
import Modal from "./Modal.vue";
defineProps({
    ourWins: Number,
    theirWins: Number,
    modelValue: Boolean,
});
</script>
