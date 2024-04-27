<template>
    <FullscreenLoader v-if="loading" />
    <div v-if="!loading && render" class="min-h-screen w-full rd-bg-pattern">
        <div class="fixed left-5 top-5">
            <Logo class="w-[70px] md:w-[150px]" v-if="!rung" />
            <span class="flex bg-white px-2 py-1 rounded" v-else>
                <div class="mr-2 font-medium">RUNG</div>
                <img :src="`/cards/${rung}.svg`" class="w-6" />
            </span>
        </div>

        <div class="fixed bottom-0 ml-5 transform">
            <Card
                v-for="(card, i) in cards"
                :card="card"
                :key="card"
                :class="{
                    'absolute cursor-pointer -bottom-14': true,
                    '-translate-y-[10vh]': clickedCard === card,
                }"
                :style="cards.includes(card) ? `left: ${i * 5.5}vw` : ''"
                :width="12"
                @mouseleave="cardUnhovered"
                @click="playCard($event, card)"
                :inactive="turnPos == me.position && !canPlayCard(card)"
                :highlighted="card[0] === rung"
                :hidden="!rung && rungSelector != me.position"
            />
        </div>

        <UserCard
            :senior="isSenior(me)"
            :name="authStore.user.username"
            class="fixed right-5 bottom-5"
            friend
            :active="turnPos && turnPos == me.position && 'left'"
            show-menu
            @click="openMenu(me)"
            :score="me?.sir_count"
        />

        <UserCard
            :senior="isSenior(teammate)"
            :name="teammate?.user.username"
            friend
            class="fixed left-1/2 -translate-x-1/2 top-5"
            :active="turnPos && turnPos == teammate?.position && 'left'"
            :show-menu="isHost && !!teammate"
            @click="isHost && openMenu(teammate)"
            :score="teammate?.sir_count"
        />

        <UserCard
            :senior="isSenior(rightOpp)"
            :name="rightOpp?.user.username"
            class="fixed top-1/2 -translate-y-1/2 right-5"
            :active="turnPos && turnPos == rightOpp?.position && 'left'"
            :show-menu="isHost && !!rightOpp"
            @click="isHost && openMenu(rightOpp)"
            :score="rightOpp?.sir_count"
        />

        <UserCard
            :senior="isSenior(leftOpp)"
            :name="leftOpp?.user.username"
            class="fixed top-1/2 -translate-y-1/2 left-5"
            :active="turnPos && turnPos == leftOpp?.position && 'right'"
            :show-menu="isHost && !!leftOpp"
            @click="isHost && openMenu(leftOpp)"
            :score="leftOpp?.sir_count"
        />

        <button
            class="fixed top-5 right-5 flex items-center justify-center"
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
                >{{ glow === true ? "+" + glowNumber : ourScore }}</span
            >
            <span
                :class="
                    scoreSpan(
                        `bg-red-600 rounded ${
                            glow === false && 'glow-animation'
                        }`
                    )
                "
                >{{ glow === false ? "+" + glowNumber : theirScore }}</span
            >
            <ChevronDownIcon class="w-5 text-white ml-1" />
        </button>

        <CardsOnTable
            :cards="cardsOnTable"
            :me="me"
            :rung="rung"
            :sirs="sirs"
            :turn-rung="turnRung"
        />

        <div class="fixed left-[23vw] top-1/2 -translate-y-1/2" v-if="sirs">
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
                <div
                    class="flex flex-col"
                    v-if="
                        (!starting && room.participants.length !== 4) ||
                        roomPaused
                    "
                >
                    <div
                        class="bg-red-600 text-sm text-center rounded-t text-white"
                    >
                        Join code
                    </div>
                    <div class="bg-white text-dark rounded-b p-2">
                        <strong>{{ room.code }}</strong>
                    </div>
                </div>

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
                        <template v-if="me.position === rungSelector">
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

        <div
            class="fixed top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 bg-black bg-opacity-95 rounded p-5 w-[70vw] h-[70vh] flex flex-col items-center justify-center text-white text-2xl"
            v-if="victory !== null || roomClosed"
        >
            <span class="mb-10">
                <template v-if="roomClosed && !isHost">
                    The room has been closed by the host.
                </template>

                <span v-if="goonCourt !== null">
                    {{
                        goonCourt
                            ? "Nice! it's a GOON COURT 🎉!"
                            : "Well...this is embarrassing, it's a GOON COURT 😔"
                    }}
                </span>
                <span v-else-if="court !== null">
                    {{
                        court
                            ? "Nice! it's a COURT 🎉!"
                            : "Well...this is embarrassing, it's a COURT 😔"
                    }}
                </span>
                <template v-else-if="victory !== null">
                    {{ victory ? "You have won!" : "You lost the game" }}
                </template>
            </span>

            <Button
                v-if="isHost || roomClosed"
                class="rd-bg"
                @click="roomClosed ? goHome() : resetRoom()"
                :disabled="!roomClosed && (!room.is_ended || reseting)"
            >
                <template v-if="roomClosed">Leave room</template>
                <template v-else>
                    {{ reseting ? "Starting..." : "Start new game" }}
                </template>
            </Button>
        </div>

        <GameMenu
            :user="openMenuFor"
            :is-self="openMenuFor?.position == me?.position"
            :is-host="me.position == 1"
            @close="openMenuFor = null"
            :restart-fn="resetRoom"
            :model-value="!!openMenuFor"
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
            :model-value="showTotals"
            @close="showTotals = false"
        />
    </div>
</template>

<script setup lang="ts">
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
import Card from "../components/Card.vue";
import Button from "../components/Button.vue";
import Totals from "../components/Totals.vue";
import {
    allCards,
    getHighCardPos,
    sortCardsByAlternateColor,
    cardNum,
    getTeamMatePosition,
} from "../utils/gameHelper";
import { ChevronDownIcon, XIcon } from "heroicons-vue3/solid";
import moment from "moment";

import CardsOnTable from "../components/CardsOnTable.vue";
import { useSound } from "@vueuse/sound";
import { maxBy } from "lodash-es";
import { mapValues } from "lodash-es";
import { useDealer } from "../composables/useDealer";

const dealer = useDealer();

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

const isHost = computed(() => me.value?.position == 1);

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

const { play: playSound } = useSound("/audio/sprite.opus", {
    // @ts-ignore
    sprite: mapValues(
        {
            start: [0, 5000],
            selectRung: [5123, 6771],
            rungSelected: [21712, 22381],
            turn: [6913, 7180],
            turnLost: [7327, 8128],
            wonSir: [8251, 9911],
            lostSir: [10079, 13512],
            victory: [13648, 16560],
            defeat: [17155, 21484],
            cardPlayed: [22651, 23348],
        },
        ([s, e]: [number, number]) => [s, e - s]
    ),
});

const glow = ref<boolean | null>(null);
const glowNumber = ref(0);

watch([teammate, me, leftOpp, rightOpp], (user, old) => {
    if (victory.value !== null) {
        playSound({ id: victory.value ? "victory" : "defeat" });
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
        glowNumber.value = weWon ? diff(0) + diff(1) : diff(2) + diff(3);
        setTimeout(() => {
            glow.value = null;
            glowNumber.value = 0;
        }, 5000);
    };

    if (weWon) {
        g(true);
        playSound({ id: "wonSir" });
    } else if (weLost) {
        g(false);
        playSound({ id: "lostSir" });
    }
});

const roomPaused = computed(() => (room.value?.participants.length || 0) < 4);

const setParticipants = (participants: RoomUser[]) => {
    room.value!.participants = participants;
    me.value = participants.find(
        (p: RoomUser) => p.user.id === authStore.user?.id
    );
    teammate.value = participants.find(
        (p) => !isOpponent(p.position) && p.position !== me.value?.position
    );
    opponents.value = participants.filter((p) => isOpponent(p.position));
};
const setValues = async (r: Room) => {
    const oldRoom = { ...room.value };

    if (r.participants.length === 4 && !r.started_at) {
        playSound({ id: "start" });
    }

    if (r.rung_selector == me.value?.position && !r.rung && r.started_at) {
        playSound({ id: "selectRung" });
    }

    room.value = r;
    me.value = r.participants.find(
        (p: RoomUser) => p.user.id === authStore.user?.id
    );
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

    if (r.turn == me.value?.position) {
        if (!oldRoom[myTurnColumn] && r[myTurnColumn]) return;
        playSound({ id: "turn" });
    }
};

const isDevelopment = computed(
    () => import.meta.env.NODE_ENV === "development"
);

const verifyRoom = async () => {
    loading.value = true;

    try {
        const res = await api.get("/rooms/current");
        const r = res.data.room;
        dealer._drawPile = [...(r.deck || [])];
        setValues(r);

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

const resetHandler = (e: any) => {
    if (e.key === "`") {
        resetRoom();
    }
};

const roomClosed = ref(false);

onMounted(() => {
    try {
        window.removeEventListener("keydown", resetHandler);
    } catch (err) {}
    window.addEventListener("keydown", resetHandler);

    if (window.pusher) {
        try {
            window.pusher.disconnect();
        } catch (error) {
            console.error(error);
        }
    }

    window.pusher = usePusher();
});

const victory = computed(() => {
    const ourScore =
        (teammate.value?.sir_count || 0) + (me.value?.sir_count || 0);
    const theirScore =
        (rightOpp.value?.sir_count || 0) + (leftOpp.value?.sir_count || 0);

    if (ourScore + theirScore < 13 || ourScore === theirScore) return null;

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
        if (window.channel) {
            window.channel.unsubscribe();
        }

        window.channel = window.pusher.subscribe(
            `private-room.${room.value?.id}`
        );
        window.channel.bind(
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
                        alert("You have been removed by the host.");
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
        [...allCards(), ...suites, "back"].map((p) => `/cards/${p}.svg`),
        () => {
            authStore.assetsLoaded = true;
            loading.value = false;
        }
    );
};

const isMyTurn = computed(() => turnPos.value == me.value?.position);
const hasTurnRungCard = computed(() =>
    cards.value.some((c) => c[0] === turnRung.value)
);

const isCardBeingPlayed = ref(false);
const canPlayCard = (card: string) => {
    if (!me.value || !room.value || roomPaused.value) return;

    if (!isMyTurn.value || isCardBeingPlayed.value) return false;
    if (
        cardNum(card) === 14 &&
        cardNum(me.value.latest_turn) === 14 &&
        room.value.last_highest_card_position == me.value.position
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
        if (isLastTurn) {
            await updateRoom({
                card_position_1: cardPositionsWithNull[1],
                card_position_2: cardPositionsWithNull[2],
                card_position_3: cardPositionsWithNull[3],
                card_position_4: cardPositionsWithNull[4],
                room_users: mapValues(roomUsers, (u) => ({
                    cards: u.cards,
                })),
            });

            await new Promise((resolve) => setTimeout(resolve, 1500));
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
            last_winner_id: userMostSirs.value?.user.id,
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
                (is_court && is_goon_court ? 1 : 0);
        }

        await updateRoom(data);
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

const cardsOnTable = ref({
    1: "",
    2: "",
    3: "",
    4: "",
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
</script>

<script lang="ts">
export const scoreSpan = (c: string) =>
    `${c} w-8 h-8 flex items-center justify-center text-white font-medium`;
</script>
