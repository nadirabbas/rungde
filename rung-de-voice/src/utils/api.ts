import axios from "axios";

export const api = axios.create({
  baseURL: process.env.API_BASE || "https://rungde.lol/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    "X-Voice-Server": 1,
  },
});

export const verifyVoiceToken = async (
  token: string,
  userId: number,
  roomId: number,
  cb: (isValid: boolean, err: string | null) => void
) => {
  false;

  try {
    await api.post("/verify-voice-token", {
      token,
      user_id: userId,
      room_id: roomId,
    });
    cb(true, null);
  } catch (error) {
    let err = "Invalid token";
    if (![422, 400].includes(error.response?.status)) {
      console.error("Error verify token: ", err);
      err = "Unknown error";
    }

    cb(false, err);
  }
};
