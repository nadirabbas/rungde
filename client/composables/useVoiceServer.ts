import { Socket, io } from "socket.io-client";

export const useVoiceServer = () => {
    if (!window.socket) {
        window.socket = io(import.meta.env.VITE_VOICE_SERVER as string);
    }

    return window.socket;
};
