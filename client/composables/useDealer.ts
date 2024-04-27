import { Dealer } from "@nadir2k/card-dealer";
import { shuffledCards } from "../utils/gameHelper";
let dealer = new Dealer<any>(shuffledCards());

export const useDealer = () => {
    return dealer;
};
