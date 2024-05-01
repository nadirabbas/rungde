<template>
    <TabGroup @change="toggleType">
        <TabList class="flex space-x-2 rounded bg-blue-900/20 mb-6">
            <Tab
                v-for="tab in tabs"
                as="template"
                :key="normalizeProps"
                v-slot="{ selected }"
            >
                <button
                    :class="[
                        'w-full rounded p-3 text-base font-medium leading-5 focus:outline-none',
                        selected
                            ? 'bg-white text-secondary shadow'
                            : 'text-white bg-white/[0.12]',
                    ]"
                >
                    {{ tab.name }}
                </button>
            </Tab>
        </TabList>
    </TabGroup>

    <form @submit.prevent="submit">
        <FieldLabel class="mb-3" dark>
            <TextField placeholder="Username" v-model="username" dark />
        </FieldLabel>
        <FieldLabel class="mb-3" dark>
            <TextField
                type="password"
                placeholder="Password"
                v-model="password"
                dark
            />
        </FieldLabel>

        <Button type="submit" :loading="loading" class="w-full">{{
            isLogin ? "Login" : "Create account"
        }}</Button>
    </form>
</template>

<script setup lang="ts">
import { normalizeProps, ref } from "vue";
import TextField from "../components/TextField.vue";
import FieldLabel from "../components/FieldLabel.vue";
import Button from "../components/Button.vue";
import { useAuthStore } from "../store/authStore";
import { useRouter } from "vue-router";
import { api } from "../api";
import { useToast } from "../composables/useToast";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";

const authStore = useAuthStore();
const router = useRouter();

const toggleType = () => {
    isLogin.value = !isLogin.value;
    username.value = "";
    password.value = "";
};
const isLogin = ref(true);
const username = ref("");
const password = ref("");
const loading = ref(false);

const toast = useToast();

const tabs = [
    {
        name: "Login",
        value: true,
    },
    {
        name: "Register",
        value: false,
    },
];

const submit = async () => {
    let error = "";
    if (!username.value) error = "Please enter your username";
    else if (username.value.includes(" "))
        error = "Username cannot contain spaces";
    if (error) {
        toast.error(error);
        return;
    }

    loading.value = true;

    try {
        const res = await api
            .post(`/auth/${isLogin.value ? "login" : "register"}`, {
                username: username.value,
                password: password.value,
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
