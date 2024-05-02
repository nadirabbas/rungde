import http from "http";
import express from "express";
import { Socket, Server } from "socket.io";
import { verifyVoiceToken } from "./api";
import cors from "cors";
import { MyRooms } from "src/MyRoomState";
import { Router } from "mediasoup/node/lib/Router";
import { Worker } from "mediasoup/node/lib/Worker";
import { startMediasoup } from "./startMediasoup";
import { createTransport, transportToOptions } from "./createTransport";
import { closePeer } from "./closePeer";
import { DtlsParameters } from "mediasoup/node/lib/fbs/web-rtc-transport";
import {
  MediaKind,
  RtpCapabilities,
  RtpParameters,
} from "mediasoup/node/lib/RtpParameters";
import { createConsumer } from "./createConsumer";

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*",
  },
});

app.use(cors());

const PORT = process.env.PORT || 3000;
const sockets = new Map<string, Socket>();
const rooms: MyRooms = {};
const userMap = new Map<
  string,
  { token: string; userId: number; socket: Socket; roomId: number }
>();
const socketToUserIdMap = new Map<string, string>();

let workers: {
  worker: Worker;
  router: Router;
}[];

export const startWebsockets = async () => {
  try {
    workers = await startMediasoup();
  } catch (err) {
    console.log(err);
    throw err;
  }
  let workerIdx = 0;

  const getNextWorker = () => {
    const w = workers[workerIdx];
    workerIdx++;
    workerIdx %= workers.length;
    return w;
  };

  const findOrCreateRoom = (roomId: string) => {
    if (!rooms[roomId.toString()]) {
      const { worker, router } = getNextWorker();

      const room = { worker, router, state: {} as any };
      rooms[roomId.toString()] = room;
      return room;
    }

    return rooms[roomId.toString()];
  };

  io.on("connection", (socket: Socket) => {
    sockets.set(socket.id, socket);

    const errBack = (message = "Unknown error") => {
      socket.emit("error", message);
    };

    socket.on(
      "joinRoom",
      async ({
        roomId,
        token,
        userId,
      }: {
        roomId: number;
        token: string;
        userId: number;
      }) => {
        if (!roomId || !token || !userId) {
          return errBack("invalid params");
        }

        verifyVoiceToken(token, userId, roomId, async (isValid, err) => {
          if (err) {
            return errBack(err);
          }

          if (!isValid) {
            return errBack("Invalid token");
          }

          socket.join(roomId.toString());

          userMap.set(userId.toString(), { roomId, token, userId, socket });
          socketToUserIdMap.set(socket.id, userId.toString());

          const { state, router } = findOrCreateRoom(roomId.toString());
          if (state[userId.toString()]) {
            closePeer(state[userId.toString()]);
          }

          const [recvTransport, sendTransport] = await Promise.all([
            createTransport("recv", router, userId.toString()),
            createTransport("send", router, userId.toString()),
          ]);

          rooms[roomId.toString()].state[userId.toString()] = {
            recvTransport,
            sendTransport,
            consumers: [],
            producer: null,
          };

          socket.emit("room_joined", {
            routerRtpCapabilities:
              rooms[roomId.toString()].router.rtpCapabilities,
            recvTransportOptions: transportToOptions(recvTransport),
            sendTransportOptions: transportToOptions(sendTransport),
          });
        });
      }
    );

    socket.on(
      "connect-mediasoup-transport",
      async ({
        roomId,
        dtlsParameters,
        userId,
        direction,
        token,
      }: {
        token: string;
        roomId: number;
        userId: number;
        direction: "recv" | "send";
        dtlsParameters: DtlsParameters;
      }) => {
        const socketUserId = socketToUserIdMap.get(socket.id) || "";
        const user = userMap.get(socketUserId);

        if (
          !user ||
          user.token !== token ||
          !rooms[roomId.toString()]?.state[userId.toString()]
        ) {
          return errBack("Unauthenticated");
        }

        const { state } = rooms[roomId.toString()];
        const transport =
          direction === "recv"
            ? state[userId.toString()].recvTransport
            : state[userId.toString()].sendTransport;

        if (!transport) {
          return errBack("Transport not found");
        }

        try {
          await transport.connect({ dtlsParameters });
          socket.emit(`${direction}-transport-connected`, { roomId });
        } catch (err) {
          console.error("Error connecting transport", err);
          return errBack(err.message);
        }
      }
    );

    socket.on(
      "create_producer",
      async ({
        roomId,
        transportId,
        userId,
        kind,
        rtpParameters,
        rtpCapabilities,
        paused,
        appData,
        token,
      }: {
        roomId: number;
        transportId: string;
        userId: number;
        token: string;
        rtpParameters: RtpParameters;
        rtpCapabilities: RtpCapabilities;
        kind: MediaKind;
        appData: any;
        paused: boolean;
      }) => {
        const socketUserId = socketToUserIdMap.get(socket.id) || "";
        const user = userMap.get(socketUserId);
        if (
          !user ||
          user.token !== token ||
          !rooms[roomId.toString()]?.state[userId.toString()]
        ) {
          return errBack("Unauthenticated");
        }

        const { state } = rooms[roomId.toString()];
        const {
          sendTransport,
          producer: previousProducer,
          consumers,
        } = state[userId.toString()];
        const transport = sendTransport;

        if (!transport) {
          return errBack("No send transport found");
        }

        try {
          if (previousProducer) {
            previousProducer.close();
            consumers.forEach((c) => c.close());
            socket.emit("producer_closed", { producerId: previousProducer.id });
            socket
              .to(roomId.toString())
              .emit("consumer_closed", { producerId: previousProducer.id });
          }

          const producer = await transport.produce({
            kind,
            rtpParameters,
            paused,
            appData: { ...appData, userId, transportId },
          });

          rooms[roomId.toString()].state[userId.toString()].producer = producer;

          // Create consumers for all users inside room
          for (const roomUserId of Object.keys(state)) {
            if (roomUserId === userId.toString()) {
              continue;
            }

            const roomUserConsumerTransport = state[roomUserId]?.recvTransport;
            if (!roomUserConsumerTransport) {
              continue;
            }

            try {
              if (!rtpCapabilities) {
                console.error("here 260");
              }
              const consumer = await createConsumer(
                rooms[roomId.toString()].router,
                producer,
                rtpCapabilities,
                roomUserConsumerTransport,
                userId.toString(),
                state[roomUserId]
              );
              const roomUserSocket = userMap.get(roomUserId)?.socket;
              if (!roomUserSocket) {
                continue;
              }

              roomUserSocket.emit("consumer_created", {
                consumer,
              });
            } catch (err) {
              console.error(err);
            }
          }

          socket.emit("producer_created", {
            producerId: producer.id,
          });
        } catch (err) {
          console.error(err);
          errBack(err.message);
        }
      }
    );

    socket.on(
      "get_consumers",
      async (d: {
        roomId: number;
        userId: number;
        rtpCapabilities: RtpCapabilities;
        token: string;
      }) => {
        const socketUserId = socketToUserIdMap.get(socket.id) || "";
        const user = userMap.get(socketUserId);
        if (
          !user ||
          user.token !== d.token ||
          !rooms[d.roomId]?.state[d.userId]
        ) {
          return;
        }

        const { state, router } = rooms[d.roomId];
        const transport = state[d.userId].recvTransport;
        if (!transport) {
          return errBack("No transport");
        }

        const consumers = [];
        for (const roomUserId of Object.keys(state)) {
          if (roomUserId === d.userId.toString()) {
            continue;
          }

          const producer = state[roomUserId].producer;
          if (!producer) {
            continue;
          }

          try {
            if (!d.rtpCapabilities) {
              console.error("here 337");
            }
            const consumer = await createConsumer(
              router,
              producer,
              d.rtpCapabilities,
              transport,
              d.userId.toString(),
              state[roomUserId]
            );
            consumers.push(consumer);
          } catch (err) {
            console.error(err);
            continue;
          }
        }

        socket.emit("consumers_created", {
          consumers,
        });
      }
    );

    socket.on("disconnect", () => {
      sockets.delete(socket.id);

      const socketUserId = socketToUserIdMap.get(socket.id) || "";
      const user = userMap.get(socketUserId);
      if (!user) return;
      const userRoomState = rooms[user.roomId]?.state[user.userId];
      if (userRoomState) closePeer(userRoomState, true);

      userMap.delete(socketUserId);
      socketToUserIdMap.delete(socket.id);
      delete rooms[user.roomId]?.state[user.userId];
    });
  });

  server.listen(PORT, () => {
    console.log(`Socket server started at port ${PORT}`);
  });
};
