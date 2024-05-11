import { AxiosRequestConfig } from "axios";
import io, { Socket } from "socket.io-client";
import { GeneralStoreState, useGeneralStore } from "./store/generalStore";
import { Store } from "pinia";
import { useAuthStore } from "./store/authStore";
import { useToast } from "./composables/useToast";
import { useVoiceServer } from "./composables/useVoiceServer";
let socket: Socket;
let generalStore: any;
let authStore: any;
let toast: any;

export const initApi = () => {
    socket = useVoiceServer();

    if (!generalStore) {
        generalStore = useGeneralStore();
    }

    if (!authStore) {
        authStore = useAuthStore();
    }

    if (!toast) {
        toast = useToast();
    }

    if (!socket.connected) generalStore.setLoading(true);

    socket.on("connect", () => {
        generalStore.setLoading(false);
    });

    socket.on("disconnect", () => {
        generalStore.setLoading(true);
    });
};

const sendRequest = (
    uri: string,
    config: AxiosRequestConfig,
    data?: any
): Promise<any> => {
    return new Promise((resolve, reject) => {
        socket.emit(
            "http-request",
            {
                uri,
                token: authStore.token,
                data,
                ...config,
            },
            ({ error, response }: any) => {
                generalStore.setLoading(false);
                if (error) {
                    toast.error(error);
                    reject(error);
                } else {
                    resolve({
                        data: response,
                    });
                }
            }
        );
    });
};

export const api = {
    get(uri: string) {
        return sendRequest(uri, { method: "GET" });
    },
    post(uri: string, data?: any): any {
        return sendRequest(uri, { method: "POST" }, data);
    },
    put(uri: string, data?: any) {
        return sendRequest(uri, { method: "PUT" }, data);
    },
    onReconnect(cb: any) {
        initApi();

        if (socket.connected) {
            cb();
        }
        socket.on("connect", () => {
            cb();
        });
    },
};
