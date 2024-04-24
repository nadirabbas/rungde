<template>
    <form @submit.prevent="submit">
        <FieldLabel label="Username" class="mb-3" dark>
            <TextField v-model="username" dark />
        </FieldLabel>

        <Button type="submit" :loading="loading" class="w-full">Play</Button>
    </form>
</template>

<script setup lang="ts">
import { ref } from "vue";
import TextField from "../components/TextField.vue";
import FieldLabel from "../components/FieldLabel.vue";
import Button from "../components/Button.vue";
import { useAuthStore } from "../store/authStore";
import { useRouter } from "vue-router";
import { api } from "../api";

const authStore = useAuthStore();
const router = useRouter();

const username = ref("");
const loading = ref(false);

const submit = async () => {
    let error = "";
    if (!username.value) error = "Please enter your username";
    else if (username.value.includes(" "))
        error = "Username cannot contain spaces";
    if (error) {
        alert(error);
        return;
    }

    loading.value = true;

    try {
        const res = await api
            .post("/auth/register", {
                username: username.value,
            })
            .then((res) => res.data);

        authStore.login(res.user);
        router.push({
            name: "Dashboard",
        });
    } catch (err) {
        console.error(err);
    }

    loading.value = false;
};
</script>
