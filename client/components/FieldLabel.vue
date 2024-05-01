<template>
    <div class="flex flex-col">
        <label
            v-if="label"
            :class="{
                'block font-medium rd-text mb-3': true,
                'text-gray-100': dark,
                'text-dark': !dark,
            }"
        >
            {{ label }}
        </label>

        <slot />
    </div>
</template>

<script setup lang="ts">
const props = defineProps({
    label: String,
    dark: Boolean,
});
</script>
