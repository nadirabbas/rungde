<template>
    <Modal v-model="isOpen" body-class=" p-3" hide-title>
        <ProfileCard :username="username" disable-editing v-if="username" />
    </Modal>
</template>

<script setup lang="ts">
import { ref, toRefs, watch } from "vue";
import Modal from "./Modal.vue";
import ProfileCard from "./ProfileCard.vue";

const isOpen = ref(false);

const emit = defineEmits(["update:modelValue"]);
const props = defineProps({
    modelValue: String,
});

const { modelValue: username } = toRefs(props);

watch(
    () => username?.value,
    () => {
        if (username?.value) {
            isOpen.value = true;
        } else {
            isOpen.value = false;
        }
    }
);

watch(
    () => isOpen.value,
    () => {
        if (!isOpen.value) {
            emit("update:modelValue", "");
        }
    }
);
</script>
