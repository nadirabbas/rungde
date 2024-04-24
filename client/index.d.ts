import { Channel } from "pusher-js";
import Pusher from "pusher-js/types/src/core/pusher";
import "vite/client";
declare global {
    interface Window {
        pusher: Pusher;
        channel: Channel;
    }
}
