const express = require("express");
const router = express.Router();
const AuthService = require("../service/AuthService");
const RedisQueueService = require("../service/RedisQueueService");
const MessageService = require("../service/MessageService");

const authService = new AuthService();
const queueService = new RedisQueueService();
const messageService = new MessageService();

router.post("/message/worker", async (req, res) => {
  console.log("POST /message/worker called");
  const token = req.header("Authorization") || "";
  const { userIdSend, userIdReceive } = req.body;
  console.log("Headers:", req.headers);
  console.log("Body:", req.body);

  if (!token || !userIdSend || !userIdReceive) {
    console.log("Missing data:", { token, userIdSend, userIdReceive });
    return res.status(400).json({ msg: "dados insuficientes" });
  }

  console.log("Validating token for user:", userIdSend);
  const isAuth = await authService.isUserAuthenticated(userIdSend, token);
  if (!isAuth) {
    console.log("Authentication failed for user:", userIdSend);
    return res.status(401).json({ msg: "not auth" });
  }

  const channelKey = `${userIdSend}:${userIdReceive}`;
  console.log("Draining queue for channel:", channelKey);
  const mensagens = await queueService.drainQueue(channelKey);
  console.log("Messages drained:", mensagens);

  for (let msg of mensagens) {
    console.log("Saving message to history:", msg);
    await messageService.saveToHistory(userIdSend, userIdReceive, msg);
  }

  console.log("All messages saved. Count:", mensagens.length);
  return res.json({ msg: "ok", savedCount: mensagens.length });
});

module.exports = router;
