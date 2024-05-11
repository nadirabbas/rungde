<template>
    <SwitchGroup @click="(e: MouseEvent) => stop && e.stopPropagation()">
        <div class="cursor-pointer flex items-center relative">
            <div
                class="w-5 h-5 absolute z-50 left-0 top-0 rounded bg-white flex items-center justify-center"
                v-show="loading"
            >
                <Spinner size="small" />
            </div>

            <Switch
                :class="{
                    ...switchClass,
                    'bg-secondary': modelValue && slider,
                    'bg-gray-400': !modelValue && slider,
                }"
                v-slot="{ checked }"
                @update:model-value="$emit('update:model-value', $event)"
                :model-value="modelValue"
                :disabled="loading"
                :dusk="dusk"
            >
                <span
                    v-if="!slider"
                    :class="{
                        'w-4 lg:w-5 aspect-square block border bg-white': true,
                        'flex items-center justify-center ': checked,

                        // Default
                        'border-green-500 bg-green-500': !legacy && checked,
                        rounded: !legacy,
                        'border-gray-300 hover:bg-link-water-2':
                            !legacy && !checked,
                        'rounded border-secondary': legacy,
                    }"
                >
                    <CheckIcon v-if="checked" class="w-3.5 text-white" />
                </span>

                <span
                    v-else
                    aria-hidden="true"
                    :class="checked ? 'translate-x-4' : 'translate-x-0'"
                    class="pointer-events-none inline-block h-[16px] w-[16px] transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                />
            </Switch>
            <SwitchLabel
                v-if="label"
                class="text-white leading-none ml-2 text-sm lg:text-base -mb-0.5 cursor-pointer select-none"
                >{{ label }}</SwitchLabel
            >
        </div>
    </SwitchGroup>
</template>

<script setup lang="ts">
import { CheckIcon } from "heroicons-vue3/solid";
import { toRefs, computed } from "vue";
import { SwitchGroup, Switch, SwitchLabel } from "@headlessui/vue";
import Spinner from "./Spinner.vue";

const props = defineProps(CheckboxProps);
const { slider } = toRefs(props);

const switchClass = computed(() => ({
    "focus:outline-none cursor-pointer": true,
    "relative inline-flex h-[20px] w-[36px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75":
        slider.value,
}));
</script>

<script lang="ts">
export const CheckboxProps = {
    modelValue: Boolean,
    loading: Boolean,
    label: String,
    colorClass: String,
    dusk: String,
    legacy: Boolean,
    slider: Boolean,
    stop: {
        type: Boolean,
        default: true,
    },
};
</script>
