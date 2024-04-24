<template>
    <input
        @input="$emit('update:modelValue', $event.target.value)"
        :value="modelValue"
        :class="{
            'p-3 rd-text rounded focus:outline-none': true,
            'border border-gray-500': !dark,
        }"
    />
</template>

<script setup lang="ts">
const props = defineProps({
    modelValue: null,
    dark: Boolean,
});
</script>
