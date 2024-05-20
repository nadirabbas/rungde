import { useAuthStore } from "../store/authStore";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

export const usePusher = (): Echo => {
    const authStore = useAuthStore();

    if (window.Echo) return window.Echo;

    window.Echo = new Echo({
        broadcaster: "reverb",
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        wsHost: import.meta.env.VITE_PUSHER_HOST,
        wsPort: import.meta.env.VITE_PUSHER_PORT,
        wssPort: import.meta.env.VITE_PUSHER_PORT,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || "http") === "https",
        enabledTransports: ["ws", "wss"],
        auth: {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
            },
        },
    });

    return window.Echo;
};
