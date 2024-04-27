import { Dealer } from "@nadir2k/card-dealer";
import { allCards } from "../utils/gameHelper";
let dealer = new Dealer<any>(allCards());

export const useDealer = () => {
    return dealer;
};
