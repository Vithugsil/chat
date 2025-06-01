const express = require('express');
const router = express.Router();
const AuthService = require('../service/AuthService');
const RedisQueueService = require('../service/RedisQueueService');
const MessageService = require('../service/MessageService');

const authService = new AuthService();
const queueService = new RedisQueueService();
const messageService = new MessageService();

/**
 * POST /message/worker
 * Header: { Authorization: token }
 * Body: { userIdSend, userIdReceive }
 * Fluxo:
 *  1) Valida token + userIdSend via Auth-API
 *  2) Se não autenticado, retorna { msg: 'not auth' }
 *  3) Senão, drena toda a fila Redis do canal `${userIdSend}${userIdReceive}`
 *  4) Para cada mensagem, chama Record-API p/ gravar no histórico
 *  5) Retorna estrutura de mensagens salvas (ou msg:'ok')
 */
router.post('/message/worker', async (req, res) => {
  const token = req.header('Authorization') || '';
  const { userIdSend, userIdReceive } = req.body;

  if (!token || !userIdSend || !userIdReceive) {
    return res.status(400).json({ msg: 'dados insuficientes' });
  }

  // 1) Valida token
  const isAuth = await authService.isUserAuthenticated(userIdSend, token);
  if (!isAuth) {
    return res.status(401).json({ msg: 'not auth' });
  }

  // 2) Drena fila
  const channelKey = `${userIdSend}${userIdReceive}`;
  const mensagens = await queueService.drainQueue(channelKey);

  // 3) Para cada mensagem, salva no Record-API
  for (let msg of mensagens) {
    await messageService.saveToHistory(userIdSend, userIdReceive, msg);
  }

  return res.json({ msg: 'ok', savedCount: mensagens.length });
});

module.exports = router;
