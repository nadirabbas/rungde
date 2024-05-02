import { Channel } from "pusher-js";
import Pusher from "pusher-js/types/src/core/pusher";
import { Socket } from "socket.io-client";
import "vite/client";
declare global {
    interface Window {
        pusher: Pusher;
        channel: Channel;
        socket: Socket;
    }
}
