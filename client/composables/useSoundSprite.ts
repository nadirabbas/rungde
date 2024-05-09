import { mapValues } from "lodash-es";
import { useSound } from "@vueuse/sound";

export const useSoundSprite = () => {
    return useSound(
        `/audio/sprite.opus?ver=${import.meta.env.VITE_SOUND_VERSION}`,
        {
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
                    ticking: [25271, 25831],
                    reaction: [25913, 26259],
                },
                ([s, e]: [number, number]) => [s, e - s]
            ),
        }
    );
};
