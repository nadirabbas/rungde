import "dotenv/config";
import { startWebsockets } from "./utils/startWebsockets";

export async function main() {
  await startWebsockets();
}
