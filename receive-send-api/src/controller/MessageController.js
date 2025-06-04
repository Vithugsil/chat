const express = require("express");
const router = express.Router();
const AuthService = require("../service/AuthService");
const RedisQueueService = require("../service/RedisQueueService");
const HttpClient = require("../utils/HttpClient");

const authService = new AuthService();
const queueService = new RedisQueueService();

const workerEndPoint = "http://localhost:8002/message/worker";

router.post("/message", async (req, res) => {
  const token = req.header("Authorization") || "";
  const { userIdSend, userIdReceive, message } = req.body;

  if (!token || !userIdSend || !userIdReceive || !message) {
    return res.status(400).json({ msg: "dados insuficientes" });
  }

  const isAuth = await authService.isUserAuthenticated(userIdSend, token);

  console.log(
    `isAuth: ${isAuth}, userIdSend: ${userIdSend}, userIdReceive: ${userIdReceive}, message: ${message}`
  );

  if (!isAuth) {
    return res.status(401).json({ msg: "not auth" });
  }

  const channelKey = `${userIdSend}:${userIdReceive}`;
  await queueService.enqueue(channelKey, message);

  return res.json({ message: "message sended with success" });
});

module.exports = router;
