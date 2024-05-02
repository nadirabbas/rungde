import { MyPeer } from "../MyPeer";

export const closePeer = (state: MyPeer, disconnected = false) => {
  console.log(disconnected ? "disconnect clean up" : "new socket cleanup");

  state.producer?.close();
  state.recvTransport?.close();
  state.sendTransport?.close();
  state.consumers.forEach((c) => c.close());
};
