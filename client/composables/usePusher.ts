import Pusher from "pusher-js";
import { useAuthStore } from "../store/authStore";

export const usePusher = () => {
    const authStore = useAuthStore();

    return new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        wsHost: import.meta.env.VITE_PUSHER_HOST,
        wsPort: import.meta.env.VITE_PUSHER_PORT,
        wssPort: import.meta.env.VITE_PUSHER_PORT,
        authEndpoint: "/broadcasting/auth",
        auth: {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
            },
        },
    });
};
