import { useToast } from "./composables/useToast";
import axios from "axios";
export const api = axios.create({
    baseURL: process.env.VITE_API_BASE || "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
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
