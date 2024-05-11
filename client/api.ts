import { useToast } from "./composables/useToast";
import axios from "axios";
import { useAuthStore } from "./store/authStore";
export const api = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

let authStore: any;

api.interceptors.request.use((config) => {
    if (!authStore) {
        authStore = useAuthStore();
    }

    const token = authStore.token;
    if (token) {
        config.headers["Authorization"] = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const toast = useToast();

        const message =
            error.response?.data.message || error.response?.data || error;

        toast.error(message);
        return Promise.reject(error);
    }
);
