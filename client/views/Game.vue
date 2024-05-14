<template>
    <FullscreenLoader v-if="loading" />
    <div
        v-if="!loading && render"
        :class="{
            'min-h-screen w-full rd-bg-pattern game': true,
            turn: isMyTurn,
        }"
    >
        <div class="fixed left-5 top-5">
            <Logo class="w-[70px] md:w-[150px]" v-if="!rung" />
            <span class="flex bg-white px-2 py-1 rounded" v-else>
                <div class="mr-2 font-medium">RUNG</div>
                <img :src="`/cards/${rung}.svg`" class="w-5" />
            </span>
        </div>

        <div class="fixed bottom-0 ml-5 transform" v-if="!isSpectating">
            <Card
                v-for="(card, i) in cards"
                :card="card"
                :key="card"
                :class="{
                    'absolute cursor-pointer -bottom-3': true,
                    '-translate-y-[10vh]': clickedCard === card,
                }"
                :style="cards.includes(card) ? `left: ${i * 5.6}vw` : ''"
                :width="12"
                @mouseleave="cardUnhovered"
                @click="playCard($event, card)"
                :inactive="turnPos == me.position && !canPlayCard(card)"
                :highlighted="card[0] === rung"
                :hidden="!rung && rungSelector != me.position"
            />
        </div>

        <UserCard
            name="Spectating"
            class="fixed left-1/2 -translate-x-1/2 bottom-5"
            show-menu
            @click="openMenu(spectator)"
            :room="room"
            is-self
            :user-id="authStore.user.id"
            is-spectating
            v-if="isSpectating"
        />

        <UserCard
            :senior="isSenior(me)"
            :name="me.user.username"
            class="fixed right-5 bottom-5"
            friend
            :active="turnPos && turnPos == me.position && 'left'"
            show-menu
            @click="openMenu(me)"
            :score="me?.sir_count"
            :score-diff="turnPos == me?.position && sirWinDiff"
            :room="room"
            is-self
            :user-id="me?.user.id"
            :stream-id="me?.stream_id"
            :show-clock="isTicking"
            :hide-emoji="isSpectating"
            :hide-voice-chat="isSpectating"
        />

        <UserCard
            :senior="isSenior(teammate)"
            :name="teammate?.user.username"
            friend
            class="fixed left-1/2 -translate-x-1/2 top-5"
            :active="turnPos && turnPos == teammate?.position && 'left'"
            :show-menu="!!teammate"
            @click="openMenu(teammate)"
            :score="teammate?.sir_count"
            :score-diff="turnPos == teammate?.position && sirWinDiff"
            :room="room"
            :user-id="teammate?.user.id"
            :stream-id="teammate?.stream_id"
            is-teammate
        />

        <UserCard
            :senior="isSenior(rightOpp)"
            :name="rightOpp?.user.username"
            class="fixed top-1/2 -translate-y-1/2 right-5"
            :active="turnPos && turnPos == rightOpp?.position && 'left'"
            :show-menu="!!rightOpp"
            @click="openMenu(rightOpp)"
            :score="rightOpp?.sir_count"
            :score-diff="turnPos == rightOpp?.position && sirWinDiff"
            :room="room"
            :user-id="rightOpp?.user.id"
            :stream-id="rightOpp?.stream_id"
        />

        <UserCard
            :senior="isSenior(leftOpp)"
            :name="leftOpp?.user.username"
            class="fixed top-1/2 -translate-y-1/2 left-5"
            :active="turnPos && turnPos == leftOpp?.position && 'right'"
            :show-menu="!!leftOpp"
            @click="openMenu(leftOpp)"
            :score="leftOpp?.sir_count"
            :score-diff="turnPos == leftOpp?.position && sirWinDiff"
            :room="room"
            is-left-opp
            :user-id="leftOpp?.user.id"
            :stream-id="leftOpp?.stream_id"
        />

        <div class="fixed top-5 right-5 flex flex-col items-end justify-end">
            <button
                class="flex items-center justify-center"
                @click="showTotals = true"
            >
                <span
                    :class="
                        scoreSpan(
                            `bg-green-600 rounded mr-1.5 ${
                                glow === true && 'glow-animation'
                            }`
                        )
                    "
                    >{{ glow === true ? "+" + sirWinDiff : ourScore }}</span
                >
                <span
                    :class="
                        scoreSpan(
                            `bg-red-600 rounded ${
                                glow === false && 'glow-animation'
                            }`
                        )
                    "
                    >{{ glow === false ? "+" + sirWinDiff : theirScore }}</span
                >
                <ChevronDownIcon class="w-5 text-white ml-1" />
            </button>

            <div id="spectator-reactions"></div>
        </div>

        <CardsOnTable
            :cards="cardsOnTable"
            :me="me"
            :rung="rung"
            :sirs="sirs"
            :turn-rung="turnRung"
        />

        <div class="fixed left-[24vw] top-1/2 -translate-y-1/2" v-if="sirs">
            <Card :width="8" card="back" />
            <div
                class="-top-2 -left-2 absolute rounded-full w-6 h-6 text-black font-black shadow-xl shadow-black flex items-center justify-center bg-yellow text-sm"
            >
                {{ sirs }}
            </div>
        </div>

        <div
            class="fixed left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2"
            v-if="!room.started_at || !room.rung || rungSelected"
        >
            <template v-if="!room.started_at || roomPaused">
                <button
                    class="flex flex-col"
                    v-if="
                        (!starting && room.participants.length !== 4) ||
                        roomPaused
                    "
                    @click="copyCode"
                >
                    <div
                        class="bg-red-600 w-full text-sm flex justify-center items-center gap-2 text-center rounded-t text-white p-1"
                    >
                        <span>Join code</span>
                    </div>
                    <div
                        class="bg-white flex items-center gap-1 text-dark rounded-b px-2 py-5"
                    >
                        <strong>{{ room.code }}</strong>
                        <DuplicateIcon class="w-5" />
                    </div>
                </button>

                <div class="font-medium text-white" v-else>
                    Starting game...
                </div>
            </template>

            <template
                v-if="
                    room.started_at &&
                    !roomPaused &&
                    (!room.rung || rungSelected)
                "
            >
                <div class="bg-white rounded p-2 min-w-[20vw]">
                    <template v-if="!room.rung">
                        <template
                            v-if="me.position === rungSelector && !isSpectating"
                        >
                            <p class="text-center font-medium text-sm mb-3">
                                Select rung
                            </p>

                            <div
                                class="grid gap-6 grid-cols-2 mb-3"
                                v-if="!rungToConfrim"
                            >
                                <button
                                    v-for="s in suites"
                                    :key="s"
                                    @click="selectRung(s)"
                                    class="flex items-center justify-center"
                                >
                                    <img
                                        :src="`/cards/${s}.svg`"
                                        class="w-8 h-8"
                                    />
                                </button>
                            </div>

                            <div
                                class="flex flex-col items-center justify-center"
                                v-else
                            >
                                <img
                                    :src="`/cards/${rungToConfrim}.svg`"
                                    class="w-8 h-8"
                                />
                                <div
                                    class="flex items-stretch w-full gap-2 mt-6"
                                >
                                    <button
                                        class="rounded bg-red-600 text-white py-2 px-3"
                                        @click="rungToConfrim = ''"
                                    >
                                        <XIcon class="w-4" />
                                    </button>

                                    <button
                                        class="w-full flex-1 text-sm rounded px-3 py-2 rd-bg text-white"
                                        @click="confirmRung"
                                        :disabled="selectingRung"
                                    >
                                        {{
                                            selectingRung
                                                ? "Confirming..."
                                                : "Confirm"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <p
                            class="text-center font-medium text-sm text-dark"
                            v-else
                        >
                            {{ rungSelectorUser?.user.username }} is selecting
                            rung...
                        </p>
                    </template>

                    <template v-if="rungSelected">
                        <p class="text-center font-medium text-sm mb-3">
                            Rung selected by
                            {{ rungSelectorUser?.user.username }}!
                        </p>

                        <div class="flex justify-center w-full">
                            <img
                                :src="`/cards/${room.rung}.svg`"
                                class="w-12 h-12 my-4"
                            />
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <TransitionFade>
            <Victory
                :victory="victory"
                :defeat="!victory"
                :goon-court="goonCourt"
                :court="court"
                :is-host="isHost"
                :restart-fn="resetRoom"
                v-if="victory !== null"
            />
        </TransitionFade>

        <div
            class="fixed top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 bg-black bg-opacity-95 rounded p-5 w-[70vw] h-[70vh] flex flex-col items-center justify-center text-white text-2xl"
            v-if="roomClosed && !isHost"
        >
            <span class="mb-10">The room has been closed by the host.</span>
            <Button class="rd-bg" @click="goHome">Leave room</Button>
        </div>

        <GameMenu
            :user="openMenuFor"
            :is-self="openMenuFor?.user.id == authStore.user.id"
            :is-host="me.position == 1 && !isSpectating"
            @close="openMenuFor = null"
            :restart-fn="resetRoom"
            :model-value="!!openMenuFor"
            :room="room"
        />

        <Totals
            :our-score="ourScore"
            :their-score="theirScore"
            :our-wins="ourTeamWins"
            :their-wins="theirTeamWins"
            :our-courts="ourCourts"
            :their-courts="theirCourts"
            :our-goon-courts="ourGoonCourts"
            :their-goon-courts="theirGoonCourts"
            v-model="showTotals"
            @close="showTotals = false"
        />

        <Chat
            v-if="room"
            :room="room"
            :channel="channel"
            :username="authStore.user.username"
        />

        <Reactions
            :room="room"
            :channel="channel"
            :user="authStore.user"
            :is-spectating="isSpectating"
            :spectator-map="spectatorMap"
            v-if="channel"
        />

        <Communications :room="room" />
    </div>
</template>

<script setup lang="ts">
import Victory from "../components/Victory.vue";
import GameMenu from "../components/GameMenu.vue";
import { usePusher } from "../composables/usePusher";
import FullscreenLoader from "../components/FullscreenLoader.vue";
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { api } from "../api";
import { Room, RoomUser, useAuthStore } from "../store/authStore";
import Logo from "../components/Logo.vue";
import UserCard from "../components/UserCard.vue";
import { preloadImages } from "../utils/preloadImages";
import { useRouter } from "vue-router";
import Communications from "../components/Communications.vue";
import Card from "../components/Card.vue";
import Button from "../components/Button.vue";
import Totals from "../components/Totals.vue";
import Reactions from "../components/Reactions.vue";
import {
    allCards,
    getHighCardPos,
    sortCardsByAlternateColor,
    cardNum,
    getTeamMatePosition,
} from "../utils/gameHelper";
import { ChevronDownIcon, XIcon } from "heroicons-vue3/solid";
import { DuplicateIcon } from "heroicons-vue3/outline";
import moment from "moment";
import copy from "copy-to-clipboard";

import CardsOnTable from "../components/CardsOnTable.vue";
import { maxBy } from "lodash-es";
import { mapValues } from "lodash-es";
import { useDealer } from "../composables/useDealer";
import { TransitionFade } from "@morev/vue-transitions";

import Chat from "../components/Chat.vue";
import { Channel } from "pusher-js";
import { useToast } from "../composables/useToast";
import { useGeneralStore } from "../store/generalStore";
import { useSoundSprite } from "../composables/useSoundSprite";

const { dealer, setDeck } = useDealer();
const toast = useToast();

const router = useRouter();
const render = ref(false);

const authStore = useAuthStore();

const room = ref<Room | null>(null);
const rung = ref<string | null>(null);
const turnRung = ref<string | null>(null);
const rungSelected = ref("");
const starting = ref(false);
const suites = ["c", "s", "h", "d"];
const rungToConfrim = ref("");
const rungSelector = ref(0);
const totalTurns = ref(0);
const showTotals = ref(false);

const me = ref<RoomUser>();
const spectator = ref<RoomUser>();
const spectatorMap = ref<Record<string, RoomUser>>({});
const isSpectating = ref(false);
const clickedCard = ref<string | null>(null);
const cards = ref<string[]>([]);
const teammate = ref<RoomUser | null>();
const opponents = ref<RoomUser[]>([]);

const court = computed(() => {
    if (ourScore.value === 13) {
        return true;
    } else if (theirScore.value === 13) {
        return false;
    }

    return null;
});

const theySelected = computed(() => {
    if (!leftOpp.value || !rightOpp.value) return null;

    const theirPositions = {
        [leftOpp.value.position.toString()]: true,
        [rightOpp.value.position.toString()]: true,
    };

    return theirPositions[rungSelector.value.toString()];
});

const goonCourt = computed(() => {
    if (!leftOpp.value || !rightOpp.value) return null;

    if (ourScore.value === 13 && theySelected.value) {
        return true;
    } else if (theirScore.value === 13 && !theySelected.value) {
        return false;
    }

    return null;
});

const isHost = computed(() => me.value?.position == 1 && !isSpectating.value);

const getOpp = (pos: "right" | "left") => {
    const myPos = me.value?.position || 0;

    if (pos === "right") {
        return opponents.value.find((o) => {
            const nextPos = myPos + 1;
            return nextPos === 5 ? o.position === 1 : o.position === nextPos;
        });
    } else {
        return opponents.value.find((o) => {
            const nextPos = myPos - 1;
            return nextPos === 0 ? o.position === 4 : o.position === nextPos;
        });
    }
};
const rightOpp = computed(() => getOpp("right"));
const leftOpp = computed(() => getOpp("left"));

const loading = ref(false);

const isOpponent = (position: number, myPosition?: number) => {
    const myPos = myPosition || me.value?.position || 0;
    const diff = Math.abs(position - myPos);
    return diff !== 0 && diff !== 2;
};

const { play: playSound } = useSoundSprite();

const glow = ref<boolean | null>(null);
const sirWinDiff = ref(0);

watch([teammate, me, leftOpp, rightOpp], (user, old) => {
    if (!room.value) return;

    if (victory.value !== null && room.value.participants.length === 4) {
        playSound({
            id: victory.value || isSpectating.value ? "victory" : "defeat",
        });
        return;
    }

    const hasWon = (idx) =>
        old[idx] && (old[idx]?.sir_count || 0) < (user[idx]?.sir_count || 0);

    const diff = (idx) =>
        (old[idx] &&
            (user[idx]?.sir_count || 0) - (old[idx]?.sir_count || 0)) ||
        0;

    const weWon = hasWon(0) || hasWon(1);
    const weLost = hasWon(2) || hasWon(3);

    const g = (v: boolean) => {
        glow.value = v;
        sirWinDiff.value = weWon ? diff(0) + diff(1) : diff(2) + diff(3);
        setTimeout(() => {
            glow.value = null;
            sirWinDiff.value = 0;
        }, 5000);
    };

    if (weWon) {
        g(true);
        playSound({ id: "wonSir" });
    } else if (weLost) {
        g(false);
        playSound({ id: isSpectating.value ? "wonSir" : "lostSir" });
    }
});

const roomPaused = computed(() => (room.value?.participants.length || 0) < 4);

const setParticipants = (participants: RoomUser[]) => {
    room.value!.participants = participants;
    me.value = participants.find(
        (p: RoomUser) => p.user.id == authStore.user?.id
    );
    teammate.value = participants.find(
        (p) => !isOpponent(p.position) && p.position != me.value?.position
    );
    opponents.value = participants.filter((p) => isOpponent(p.position));
};

const setParticipantById = (id, user) => {
    if (!room.value) return;
    room.value.participants = room.value.participants.map((r) =>
        r.user_id == id ? user : r
    );
    setParticipants(room.value.participants);
};

const isTicking = ref(false);
const isWaitingForNextTurn = ref(false);

const setValues = async (r: Room, isEvent = true) => {
    const oldRoom = { ...room.value };

    if (oldRoom.event_counter && oldRoom.event_counter >= r.event_counter) {
        // Older event received after newer
        console.log("Discarding older event", {
            newState: r,
            oldState: oldRoom,
        });
        return;
    }

    if (!isEvent) {
        spectatorMap.value = r.spectators.reduce((acc, s) => {
            acc[s.user.id] = s;
            return acc;
        }, {});
    }

    if (r.participants.length === 4 && !r.started_at) {
        playSound({ id: "start" });
    }

    if (r.rung_selector == me.value?.position && !r.rung && r.started_at) {
        playSound({ id: "selectRung" });
    }

    room.value = r;

    if (!cardsOnTableArray.value.length) {
        isWaitingForNextTurn.value = false;
    }

    me.value = r.participants.find(
        (p: RoomUser) => p.user.id == authStore.user?.id
    );

    if (!me.value) {
        const mySpectator = r.spectators.find(
            (p: RoomUser) => p.user.id == authStore.user?.id
        );

        if (mySpectator) {
            me.value = r.participants.find((p: RoomUser) => p.position == 1);
            spectator.value = mySpectator;
            isSpectating.value = true;
        }
    }

    const myPos = me.value?.position || 0;
    opponents.value = r.participants.filter((p) => isOpponent(p.position));
    teammate.value = r.participants.find(
        (p) => !isOpponent(p.position) && p.position !== myPos
    );
    turnPos.value = r.turn;
    cards.value = me.value?.cards || [];
    render.value = true;
    rungSelector.value = r.rung_selector;
    turnRung.value = r.turn_rung;
    rung.value = r.rung;
    totalTurns.value = r.total_turns;

    cardsOnTable.value = {
        1: r.card_position_1,
        2: r.card_position_2,
        3: r.card_position_3,
        4: r.card_position_4,
    };
    sirs.value = r.folded_deck_count;

    if (r.participants.length === 4 && !r.started_at) {
        startRoom();
    }

    const myTurnColumn = `card_position_${me.value?.position}`;

    if (r.turn && r.turn_rung) {
        playSound({ id: "cardPlayed" });
    }

    if (r.turn == me.value?.position && !isSpectating.value) {
        if (!oldRoom[myTurnColumn] && r[myTurnColumn]) return;
        playSound({ id: "turn" });

        const playTick = async () => {
            if (
                !isTicking.value ||
                isSpectating.value ||
                isWaitingForNextTurn.value
            )
                return;
            playSound({ id: "ticking" });
            await new Promise((resolve) => setTimeout(resolve, 1000));
            playTick();
        };
        const totalTurns = parseInt(r.total_turns.toString());
        setTimeout(() => {
            if (totalTurns == room.value?.total_turns) {
                isTicking.value = true;
                playTick();
                const unwatch = watch(room, (newRoom) => {
                    if (newRoom?.total_turns != totalTurns) {
                        unwatch();
                        isTicking.value = false;
                    }
                });
            }
        }, 10 * 1000);
    }
};

const isDevelopment = computed(
    // @ts-ignore
    () => import.meta.env.NODE_ENV === "development"
);

const generalStore = useGeneralStore();

const verifyRoom = async () => {
    loading.value = true;

    try {
        const res = await api.get("/rooms/current");
        const r = res.data.room;
        dealer._drawPile = [...(r.deck || [])];
        setValues(r, false);

        await initSocket();
    } catch (err) {
        router.push({
            name: "Dashboard",
        });
        console.error(err);
        loading.value = false;
    }
};

const updateRoom = (data: any) => {
    return api.put(`/rooms/${room.value?.id}`, data);
};

const startRoom = async () => {
    if (!isHost.value) return;

    dealer.reset();

    for (let i = 0; i < 100; i++) {
        dealer.shuffle();
    }

    starting.value = true;
    setTimeout(async () => {
        const mostSirUser = room.value?.last_winner_id
            ? getUserById(room.value?.last_winner_id)
            : null;

        const rungSelector =
            mostSirUser?.position || Math.floor(Math.random() * 4) + 1;

        await updateRoom({
            started_at: moment().toISOString(),
            rung_selector: rungSelector,
            room_users: distributeCards(rungSelector),
            deck: dealer._drawPile,
            new_deck: [],
        });

        starting.value = false;
    }, 5000);
};

const rungSelectorUser = computed(() => getUserByPosition(rungSelector.value));

const distributeCards = (rungSelectorPos) => {
    const positionOrder = [0, 0, 0].reduce(
        (acc) => {
            const nextValue = acc[acc.length - 1] + 1;
            acc.push(nextValue > 4 ? nextValue - 4 : nextValue);
            return acc;
        },
        [parseInt(rungSelectorPos)]
    );

    const roomUsers = rung.value
        ? positionOrder.reduce((acc, pos) => {
              acc[pos] = {
                  cards: getUserByPosition(pos)?.cards || [],
              };
              return acc;
          }, {})
        : {};

    const d = (p, n) => {
        roomUsers[p] = {
            cards: (roomUsers[p]?.cards || []).concat(dealer.draw(n)),
        };
    };

    for (let i = 0; i < (rung.value ? 2 : 1); i++) {
        positionOrder.forEach((pos) => {
            d(pos, rung.value ? 4 : 5);
        });
    }

    return mapValues(roomUsers, (u) => ({
        cards: sortCardsByAlternateColor(u.cards, rung.value),
    }));
};

const startTurn = () => {
    if (!isHost.value) return;

    return updateRoom({
        turn: rungSelector.value,
        room_users: distributeCards(rungSelector.value),
        deck: dealer._drawPile,
    });
};

const reseting = ref(false);
const resetRoom = async (resetScore = false) => {
    if (!isHost.value) return false;

    reseting.value = true;

    try {
        await updateRoom({
            latest_turn: null,
            latest_turn_position: null,
            ended_at: null,
            started_at: null,
            rung: null,
            rung_selector: null,
            turn: null,
            room_users: {
                1: {
                    cards: [],
                    sir_count: 0,
                },
                2: {
                    cards: [],
                    sir_count: 0,
                },
                3: {
                    cards: [],
                    sir_count: 0,
                },
                4: {
                    cards: [],
                    sir_count: 0,
                },
            },
            card_position_1: null,
            card_position_2: null,
            card_position_3: null,
            card_position_4: null,
            total_turns: 0,
            folded_deck_count: 0,
            turn_rung: null,
            last_highest_card_position: null,
            team_1_3_wins: resetScore ? 0 : undefined,
            team_2_4_wins: resetScore ? 0 : undefined,
            team_1_3_goon_courts: resetScore ? 0 : undefined,
            team_2_4_goon_courts: resetScore ? 0 : undefined,
            team_1_3_courts: resetScore ? 0 : undefined,
            team_2_4_courts: resetScore ? 0 : undefined,
            deck: [],
        });
    } catch (error) {
        console.error(error);
    }

    reseting.value = false;
};

const roomClosed = ref(false);
const pusher = usePusher();
const channel = ref<Channel>();

const victory = computed(() => {
    const ourScore =
        (teammate.value?.sir_count || 0) + (me.value?.sir_count || 0);
    const theirScore =
        (rightOpp.value?.sir_count || 0) + (leftOpp.value?.sir_count || 0);

    if (ourScore + theirScore < 13 || ourScore === theirScore) return null;

    if (isSpectating.value) {
        return true;
    }

    if (ourScore > theirScore) {
        return true;
    } else if (ourScore < theirScore) {
        return false;
    }
});
const goHome = () => (window.location.href = "/");
const initSocket = async () => {
    loading.value = true;

    try {
        channel.value = pusher.subscribe(`private-room.${room.value?.id}`);
        channel.value.bind("userchanged", ({ roomUser }) => {
            setParticipantById(roomUser.user_id, roomUser);
        });
        channel.value.bind(
            "spectator-event",
            ({ joined, spectator, leftId }) => {
                let spec = spectator;
                if (leftId) {
                    spec = spectatorMap.value[leftId];
                    room.value!.spectators = room.value!.spectators.filter(
                        (s) => s.user.id != leftId
                    );
                } else {
                    room.value!.spectators.push(spectator);
                    spectatorMap.value[spectator.user.id] = spectator;
                }

                toast.error(
                    `${spec.user.username} ${
                        joined ? "started" : "stopped"
                    } spectating`
                );
                delete spectatorMap.value[leftId];
            }
        );

        channel.value.bind(
            "updated",
            ({
                room: r,
                closed,
                leftPos,
                removedPos,
            }: {
                room: Room;
                closed: Boolean;
                leftPos: string;
                removedPos: string;
            }) => {
                if (leftPos || removedPos) {
                    if (removedPos === me.value?.position.toString()) {
                        toast.error("You have been removed by the host.");
                        goHome();
                        return;
                    }

                    setParticipants(r.participants);
                    resetRoom(true);
                    return;
                }

                if (closed) {
                    roomClosed.value = true;
                    return;
                }

                if (r.rung && !rung.value) {
                    rungSelected.value = r.rung;
                    playSound({ id: "rungSelected" });
                    rung.value = r.rung;
                    setTimeout(() => {
                        rungSelected.value = "";
                        startTurn();
                    }, 5000);
                }
                setValues(r);
            }
        );

        loadAssets();
    } catch (err) {
        loading.value = false;
        console.error(err);
    }
};

const loadAssets = () => {
    if (authStore.assetsLoaded) {
        loading.value = false;
        return;
    }

    console.log("Preloading assets...");

    loading.value = true;

    preloadImages(
        suites.map((p) => `/cards/${p}.svg`),
        () => {
            authStore.assetsLoaded = true;
            loading.value = false;
        }
    );
};

const hasTurnRungCard = computed(() =>
    cards.value.some((c) => c[0] === turnRung.value)
);

const isCardBeingPlayed = ref(false);
const canPlayCard = (card: string) => {
    if (
        !me.value ||
        !room.value ||
        roomPaused.value ||
        isWaitingForNextTurn.value
    )
        return;

    if (!isMyTurn.value || isCardBeingPlayed.value) return false;
    if (
        cardNum(card) === 14 &&
        cardNum(me.value.latest_turn) === 14 &&
        room.value.last_highest_card_position == me.value.position &&
        room.value.total_turns < 48
    ) {
        return false;
    }

    if (turnRung.value) {
        if (hasTurnRungCard.value) {
            return card[0] === turnRung.value;
        }
    }

    return true;
};

const getUserByPosition = (position: number) => {
    return room.value?.participants.find((u) => u?.position == position);
};

const getUserById = (id: number) => {
    return room.value?.participants.find((u) => u?.user.id == id);
};

const cardHovered = (e: any, card: string) => {
    if (!canPlayCard(card)) return;
    clickedCard.value = card;
};

const cardUnhovered = (e: any) => {
    clickedCard.value = null;
};

const userMostSirs = computed(() => {
    return maxBy(
        room.value?.participants || [],
        (p) => p.sir_count
    ) as RoomUser;
});

const playCard = async (e: any, card: string) => {
    if (!canPlayCard(card) || !me.value) return;

    if (!clickedCard.value) {
        cardHovered(e, card);
        return;
    }

    isTicking.value = false;
    isCardBeingPlayed.value = true;

    const oldCards = [...cards.value];
    const oldCardsOnTable = { ...cardsOnTable.value };
    const oldTurnRung = turnRung.value?.toString();
    const oldTurn = turnPos.value?.toString();
    const oldLastHighestCardPosition =
        room.value?.last_highest_card_position || 0;
    const oldSirs = parseInt(sirs.value.toString());

    const newCards = cards.value.filter((c) => c !== card);
    const newTotalTurns = totalTurns.value + 1;
    const isLastTurn = newTotalTurns % 4 === 0;
    const isVeryLastTurn = newTotalTurns === 52;

    cardsOnTable.value[me.value.position.toString()] = card;

    const newHighestCardPosition = getHighCardPos(
        cardsOnTable.value,
        turnRung.value,
        rung.value
    );
    const winnerPosition =
        isLastTurn &&
        (newHighestCardPosition == oldLastHighestCardPosition ||
            isVeryLastTurn) &&
        (!ourScore.value && !theirScore.value ? newTotalTurns >= 12 : true)
            ? newHighestCardPosition
            : null;
    const iAmWinner = winnerPosition == me.value.position;

    const cardPositionsWithNull = {
        ...cardsOnTable,
        [me.value.position]: card,
    };
    const newCardsOnTable = isLastTurn
        ? {
              1: null,
              2: null,
              3: null,
              4: null,
          }
        : cardPositionsWithNull;
    const newTurn = isVeryLastTurn
        ? 0
        : isLastTurn
        ? newHighestCardPosition
        : turnPos.value === 4
        ? 1
        : turnPos.value + 1;
    const newSirs = winnerPosition
        ? 0
        : isLastTurn
        ? sirs.value + 1
        : sirs.value;

    clickedCard.value = null;
    cards.value = newCards;
    sirs.value = newSirs;

    const roomUsers: any = {
        [me.value.position]: {
            cards: newCards,
            latest_turn: card,
            sir_count: iAmWinner
                ? me.value.sir_count + (oldSirs + 1)
                : me.value.sir_count,
        },
    };

    if (winnerPosition && !roomUsers[winnerPosition]) {
        const winner = getUserByPosition(winnerPosition);
        if (winner) {
            roomUsers[winnerPosition] = {
                cards: winner.cards,
                sir_count: winner.sir_count + oldSirs + 1,
            };
        }
    }

    try {
        let initialData = {};
        if (isLastTurn && room.value) {
            initialData = {
                card_position_1: cardPositionsWithNull[1],
                card_position_2: cardPositionsWithNull[2],
                card_position_3: cardPositionsWithNull[3],
                card_position_4: cardPositionsWithNull[4],
                room_users: mapValues(roomUsers, (u) => ({
                    cards: u.cards,
                })),
            };
        }

        const data = {
            latest_turn: card,
            latest_turn_position: me.value.position,
            turn: newTurn,
            room_users: roomUsers,
            card_position_1: newCardsOnTable[1],
            card_position_2: newCardsOnTable[2],
            card_position_3: newCardsOnTable[3],
            card_position_4: newCardsOnTable[4],
            total_turns: newTotalTurns,
            folded_deck_count: newSirs,
            turn_rung: isLastTurn ? null : turnRung.value ? undefined : card[0],
            last_highest_card_position: isLastTurn
                ? winnerPosition
                    ? null
                    : newHighestCardPosition
                : undefined,
            ended_at: isVeryLastTurn ? moment().toISOString() : undefined,
            new_deck: [card, ...(room.value?.new_deck || [])],
        };

        if (room.value && isVeryLastTurn && winnerPosition) {
            const scoreByPos = [1, 2, 3, 4].reduce((acc, pos) => {
                const user = getUserByPosition(pos);
                if (user?.position == winnerPosition) {
                    acc[pos] = user.sir_count + oldSirs + 1;
                } else {
                    acc[pos] = user?.sir_count;
                }

                return acc;
            }, {});

            const team_1_3_score = scoreByPos[1] + scoreByPos[3];
            const team_2_4_score = scoreByPos[2] + scoreByPos[4];
            const did_1_3_select =
                rungSelector.value == 1 || rungSelector.value == 3;
            const is_goon_court = did_1_3_select
                ? team_2_4_score === 13
                : team_1_3_score === 13;
            const is_court = team_1_3_score === 13 || team_2_4_score === 13;

            const winColumn =
                team_1_3_score > team_2_4_score ? "team_1_3" : "team_2_4";

            data[winColumn + "_wins"] = room.value[winColumn + "_wins"] + 1;
            data[winColumn + "_goon_courts"] =
                room.value[winColumn + "_goon_courts"] +
                (is_goon_court ? 1 : 0);
            data[winColumn + "_courts"] =
                room.value[winColumn + "_courts"] +
                (is_court && !is_goon_court ? 1 : 0);

            const winningParticipants = room.value.participants.filter((p) => {
                const pos = parseInt(p.position.toString());
                return pos % 2 !== (team_1_3_score > team_2_4_score ? 0 : 1);
            });

            data["last_winner_id"] = maxBy(
                winningParticipants,
                (p) => p.sir_count
            )?.user.id;
        }

        if (isLastTurn) {
            isTicking.value = false;
            isWaitingForNextTurn.value = true;
        }
        await updateRoom(
            isLastTurn
                ? {
                      ...initialData,
                      delayed_update: data,
                  }
                : data
        );
    } catch (err) {
        cards.value = oldCards;
        cardsOnTable.value = oldCardsOnTable;
        sirs.value = oldSirs;
        totalTurns.value = newTotalTurns - 1;
        turnRung.value = oldTurnRung || null;
        turnPos.value = parseInt(oldTurn);
        room.value!.last_highest_card_position = oldLastHighestCardPosition;
        console.error(err);
    }

    isCardBeingPlayed.value = false;
};

const ourTeamWins = computed(() => {
    return parseInt(me.value?.position.toString() || "0") % 2 === 0
        ? room.value?.team_2_4_wins
        : room.value?.team_1_3_wins;
});

const theirTeamWins = computed(() => {
    return parseInt(me.value?.position.toString() || "0") % 2 === 0
        ? room.value?.team_1_3_wins
        : room.value?.team_2_4_wins;
});

const ourGoonCourts = computed(() => {
    if (!room.value) return 0;
    return parseInt(me.value?.position.toString() || "0") % 2 === 0
        ? room.value.team_2_4_goon_courts
        : room.value.team_1_3_goon_courts;
});

const theirGoonCourts = computed(() => {
    if (!room.value) return 0;
    return parseInt(me.value?.position.toString() || "0") % 2 === 0
        ? room.value.team_1_3_goon_courts
        : room.value.team_2_4_goon_courts;
});

const ourCourts = computed(() => {
    if (!room.value) return 0;
    return parseInt(me.value?.position.toString() || "0") % 2 === 0
        ? room.value.team_2_4_courts
        : room.value.team_1_3_courts;
});

const theirCourts = computed(() => {
    if (!room.value) return 0;
    return parseInt(me.value?.position.toString() || "0") % 2 === 0
        ? room.value.team_1_3_courts
        : room.value.team_2_4_courts;
});

onMounted(verifyRoom);

const ourScore = computed(
    () => (teammate.value?.sir_count || 0) + (me.value?.sir_count || 0)
);
const theirScore = computed(
    () => (rightOpp.value?.sir_count || 0) + (leftOpp.value?.sir_count || 0)
);

const isSenior = (user: RoomUser) => {
    if (!user) return false;

    const highCardPosString = getHighCardPos(
        cardsOnTable.value,
        turnRung.value,
        rung.value
    );

    const pos = parseInt(user.position.toString());
    const highCardPos = parseInt(highCardPosString?.toString());
    const posCardOnTable = cardsOnTable.value[pos];

    if (room.value?.last_highest_card_position == pos) {
        return !posCardOnTable || highCardPos === pos;
    } else {
        return highCardPos === pos;
    }
};

const turnPos = ref(0);
const isMyTurn = computed(() => turnPos.value == me.value?.position);

const cardsOnTable = ref({
    1: "",
    2: "",
    3: "",
    4: "",
});
const cardsOnTableArray = computed(() => {
    if (!room.value) {
        return [];
    }

    return [
        room.value.card_position_1,
        room.value.card_position_2,
        room.value.card_position_3,
        room.value.card_position_4,
    ].filter((c) => c);
});

const sirs = ref(0);

const selectRung = (rung: string) => {
    rungToConfrim.value = rung;
};

const selectingRung = ref(false);
const confirmRung = async () => {
    selectingRung.value = true;

    try {
        await updateRoom({
            rung: rungToConfrim.value,
        });
        rungToConfrim.value = "";
    } catch (err) {
        console.error(err);
    }

    selectingRung.value = false;
};

const openMenuFor = ref<RoomUser>();
const openMenu = (user: RoomUser) => {
    openMenuFor.value = user;
};
const copyCode = () => {
    try {
        if (!room.value) return;
        copy(room.value.code);
        toast.success("Code copied to clipboard");
    } catch (error) {
        toast.error("There was a problem copying the code");
    }
};
</script>

<script lang="ts">
export const scoreSpan = (c: string) =>
    `${c} w-8 h-8 flex items-center justify-center text-white font-medium`;
</script>

<style lang="scss">
.game.turn {
    animation: turn 1.3s ease-out infinite;
}

$c: rgba(255, 255, 255, 0.35);

@keyframes turn {
    0% {
        box-shadow: inset 0 0 10px 2px $c;
    }
    50% {
        box-shadow: inset 0 0 20px 20px $c;
    }
    100% {
        box-shadow: inset 0 0 10px 2px $c;
    }
}
</style>
