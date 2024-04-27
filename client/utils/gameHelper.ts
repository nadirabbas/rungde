import { maxBy, groupBy, mapValues } from "lodash-es";
export const cardNum = (card?: string) =>
    card ? parseInt(card.substring(1)) : 0;

export const getHighestCard = (
    cards: string[],
    turnRung?: string | null,
    rung?: string | null
) => {
    if (rung) {
        const rungCards = cards.filter((card) => card[0] === rung);
        if (rungCards.length) {
            return maxBy(rungCards, cardNum);
        }
    }

    if (turnRung) {
        const turnRungCards = cards.filter((card) => card[0] === turnRung);
        if (turnRungCards.length) {
            return maxBy(turnRungCards, cardNum);
        }
    }

    return maxBy(cards, cardNum);
};

export const getHighCardPos = (
    cards: Record<number, string>,
    turnRung?: string | null,
    rung?: string | null
) => {
    const highestCard = getHighestCard(
        Object.values(cards).filter(Boolean),
        turnRung,
        rung
    );
    // @ts-ignore
    return Object.entries(cards).find(
        ([position, card]) => card === highestCard
    )?.[0] as number;
};

export const allCards = () => {
    let cards: string[] = [];
    ["c", "h", "s", "d"].forEach((suite: string) => {
        for (let i = 2; i <= 14; i++) {
            cards.push(`${suite}${i}`);
        }
    });

    return cards;
};

export const shuffledCards = () => {
    const cards = allCards();
    const shuffled: string[] = [];
    for (let i = 0; i < 52; i++) {
        const randomIndex = Math.floor(Math.random() * cards.length);
        shuffled.push(cards[randomIndex]);
        cards.splice(randomIndex, 1);
    }

    return shuffled;
};

export const sortCardsByAlternateColor = (
    cards: string[],
    rung?: string | null
) => {
    const groupedBySuite = groupBy(cards, (card) => card[0]);
    const sortedByRank = mapValues(groupedBySuite, (cards) =>
        cards.sort((a, b) => cardNum(b) - cardNum(a))
    );
    const getCardsBySuite = (suite: string) => {
        const cards = sortedByRank[suite] || [];
        delete sortedByRank[suite];
        return cards;
    };

    const order = rung
        ? [0, 0, 0]
              .reduce(
                  (acc) => {
                      const lastRung = acc[acc.length - 1];
                      acc.push(
                          //   @ts-ignore
                          {
                              h: "c",
                              c: "d",
                              d: "s",
                              s: "h",
                          }[lastRung]
                      );
                      return acc;
                  },
                  [rung]
              )
              .reverse()
        : ["d", "c", "h", "s"];

    return order.map(getCardsBySuite).flat();
};

export const pickRandomCards = (
    count: number,
    exclude: string[] = [],
    rung?: string | null
) => {
    const cards = allCards().filter((card) => !exclude.includes(card));
    const randomCards: string[] = [];
    for (let i = 0; i < count; i++) {
        const randomIndex = Math.floor(Math.random() * cards.length);
        randomCards.push(cards[randomIndex]);
        cards.splice(randomIndex, 1);
    }

    return sortCardsByAlternateColor(randomCards, rung);
};

export const getTeamMatePosition = (myPosition: number | string) => {
    const position = parseInt(myPosition.toString());
    const teammatePosition = position + 2;
    return teammatePosition > 4 ? teammatePosition - 4 : teammatePosition;
};

export const getNextPosition = (myPosition: number | string) => {
    const position = parseInt(myPosition.toString());
    return (position % 4) + 1;
};

export const getPreviousPosition = (myPosition: number | string) => {
    const position = parseInt(myPosition.toString());
    return position === 1 ? 4 : position - 1;
};
