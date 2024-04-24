import { defineStore } from "pinia";

export interface GeneralStoreState {
    loading: boolean;
    started: boolean;
}

export const useGeneralStore = defineStore("general", {
    state: (): GeneralStoreState => ({
        loading: false,
        started: false,
    }),
    actions: {
        setLoading(loading: boolean) {
            this.loading = loading;
        },
    },
    persist: {
        paths: ["started"],
    },
});
