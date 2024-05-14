<template>
    <Modal
        :model-value="modelValue"
        @close="$emit('close')"
        title="Total score"
        hint="Shortcut: T"
    >
        <div
            class="flex items-center justify-between text-lg mb-4 w-full"
            v-for="(t, i) in totals"
            :key="i"
        >
            <p class="text-base mr-6">{{ t.name }}</p>

            <div class="flex items-center gap-4">
                <span :class="scoreCont('bg-green-600 rounded')">{{
                    t.our
                }}</span>
                <span class="scale-x-150">v</span>
                <span :class="scoreCont('bg-red-600 rounded')">{{
                    t.their
                }}</span>
            </div>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import { computed, toRefs, watch, watchEffect } from "vue";
import { scoreCont } from "../views/Game.vue";
import Modal from "./Modal.vue";
import { useMagicKeys } from "@vueuse/core";
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

const totals = computed(() => [
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
]);
const emit = defineEmits(["update:model-value"]);
const { t } = useMagicKeys();
watch(t, () => {
    if (!t.value) return;
    emit("update:model-value", !refs.modelValue.value);
});
</script>
