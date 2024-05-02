import { defineStore } from "pinia";

export interface GeneralStoreState {
    loading: boolean;
    started: boolean;
    hasUserInteracted: boolean;
    tempRoomId: number;
}

export const useGeneralStore = defineStore("general", {
    state: (): GeneralStoreState => ({
        loading: false,
        started: false,
        hasUserInteracted: false,
        tempRoomId: 0,
    }),
    actions: {
        setLoading(loading: boolean) {
            this.loading = loading;
        },
    },
    persist: {
        paths: ["tempRoomId"],
    },
});
