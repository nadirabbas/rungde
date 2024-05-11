import { defineStore } from "pinia";

export interface RoomUser {
    id: number;
    user: Omit<User, "room">;
    position: 1 | 2 | 3 | 4;
    cards: string[];
    sir_count: number;
    latest_turn: string;
    stream_id: null | string;
    user_id: number;
}

export interface Room {
    id: number;
    user_id: number;
    code: string;
    ended_at: string | null;
    started_at: string | null;
    is_ended: boolean;
    participants: RoomUser[];
    turn: 1 | 2 | 3 | 4;
    rung: string | null;
    rung_selector: number;
    turn_rung: string;
    card_position_1: string;
    card_position_2: string;
    card_position_3: string;
    card_position_4: string;
    folded_deck_count: number;
    total_turns: number;
    last_highest_card_position: number;
    latest_turn: string;
    latest_turn_position: number;
    last_winner_id: number | null;
    team_1_3_wins: number;
    team_2_4_wins: number;
    deck: string[];
    new_deck: string[];
    team_1_3_goon_courts: number;
    team_2_4_goon_courts: number;
    team_1_3_courts: number;
    team_2_4_courts: number;
}

export interface User {
    id: number;
    username: string;
    room_id: number;
    room: Room | null;
    sirs: number;
    games_played: number;
    games_won: number;
    avatar: string;
    courts: number;
    goon_courts: number;
}

export interface AuthStoreState {
    user: User | null;
    assetsLoaded: boolean;
    token: string | null;
}

export const useAuthStore = defineStore("auth", {
    state: (): AuthStoreState => ({
        user: null,
        assetsLoaded: false,
        token: null,
    }),
    getters: {
        room(): Room {
            return this.user?.room;
        },
    },
    actions: {
        setUser(user: User) {
            this.user = user;
        },
        logout() {
            this.user = null;
            this.token = null;
        },
        login(user: User, token?: string) {
            this.user = user;
            if (token) {
                this.token = token;
            }
        },
        setRoom(room: Room) {
            this.user!.room = room;
            this.user!.room_id = room.id;
        },
    },
    persist: true,
});
