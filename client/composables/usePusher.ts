import Pusher from "pusher-js";
import { useAuthStore } from "../store/authStore";

export const usePusher = () => {
    const authStore = useAuthStore();

    return new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        authEndpoint: "/broadcasting/auth",
        auth: {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
            },
        },
    });
};
