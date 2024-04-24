import Pusher from "pusher-js";

export const usePusher = () => {
    return new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        authEndpoint: "/broadcasting/auth",
    });
};
