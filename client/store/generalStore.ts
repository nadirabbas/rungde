import { defineStore } from "pinia";

export interface GeneralStoreState {
    loading: boolean;
    started: boolean;
    hasUserInteracted: boolean;
}

export const useGeneralStore = defineStore("general", {
    state: (): GeneralStoreState => ({
        loading: false,
        started: false,
        hasUserInteracted: false,
    }),
    actions: {
        setLoading(loading: boolean) {
            this.loading = loading;
        },
    },
});
