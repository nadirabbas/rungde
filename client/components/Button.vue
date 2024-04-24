<template>
    <button
        v-bind="$attrs"
        class="p-3 rd-text font-medium border-none rounded bg-white text-dark focus:outline-none"
        :disabled="loading"
    >
        <slot v-if="!loading" />
        <span v-else>Please wait...</span>
    </button>
</template>

<script setup lang="ts">
defineProps({
    loading: Boolean,
});
</script>
