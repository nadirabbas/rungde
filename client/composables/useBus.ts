import mitt from "mitt";
const bus = mitt();
export const useBus = () => bus;
