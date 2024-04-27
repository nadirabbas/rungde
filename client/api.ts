import axios from "axios";
import { useToast } from "vue-toast-notification";

export const api = axios.create({
    baseURL: "/api",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const toast = useToast({
            position: "bottom",
        });

        const message =
            error.response?.data.message || error.response?.data || error;

        toast.error(message);
        return Promise.reject(error);
    }
);
