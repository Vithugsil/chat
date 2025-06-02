const express = require("express");
const router = express.Router();
const AuthService = require("../service/AuthService");
const MessageService = require("../service/MessageService");

const authService = new AuthService();
const messageService = new MessageService();

router.get("/message", async (req, res) => {
  const token = req.header("Authorization") || "";
  console.log("token:", token);

  const userId = parseInt(req.query.user || "0", 10);
  console.log("userId:", userId);

  if (!token || !userId) {
    console.log("dados insuficientes");
    return res.status(400).json({ msg: "dados insuficientes" });
  }

  const isAuth = await authService.isUserAuthenticated(userId, token);

  if (!isAuth) {
    console.log("not auth");
    return res.status(401).json({ msg: "not auth" });
  }

  const msgs = await messageService.getMessagesById(userId);

  return res.json(msgs);
});

module.exports = router;
