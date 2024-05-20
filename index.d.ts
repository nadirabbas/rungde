import Pusher from "pusher-js/types/src/core/pusher";
import { Socket } from "socket.io-client";
import Echo from "laravel-echo";
import "vite/client";
declare global {
    interface Window {
        Echo: Echo;
        Pusher: typeof Pusher;
        socket: Socket;
    }
}

declare module "laravel-echo" {
    interface Channel {
        whisper(eventName: string, data: any): void;
    }
}
