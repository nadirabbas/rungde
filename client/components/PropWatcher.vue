<template></template>

<script setup lang="ts">
import { toRefs, watch } from "vue";

const props = defineProps({
    watch: {
        type: null,
        required: true,
    },
});

const { watch: propToWatch } = toRefs(props);
const emit = defineEmits(["change"]);

watch(propToWatch, () => {
    emit("change", propToWatch.value);
});
</script>
