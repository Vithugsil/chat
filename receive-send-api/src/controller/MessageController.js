const express = require('express');
const router = express.Router();
const AuthService = require('../service/AuthService');
const RedisQueueService = require('../service/RedisQueueService');

const authService = new AuthService();
const queueService = new RedisQueueService();

/**
 * POST /message
 * Header: { Authorization: token }
 * Body: { userIdSend, userIdReceive, message }
 * Fluxo:
 *  1) Valida token + userIdSend via Auth-API
 *  2) Se não autenticado, retorna { msg: 'not auth' }
 *  3) Senão, faz enqueue na fila Redis (key = `${userIdSend}${userIdReceive}`)
 *  4) Retorna sucesso
 */
router.post('/message', async (req, res) => {
  const token = req.header('Authorization') || '';
  const { userIdSend, userIdReceive, message } = req.body;

  if (!token || !userIdSend || !userIdReceive || !message) {
    return res.status(400).json({ msg: 'dados insuficientes' });
  }

  // 1) Valida token
  const isAuth = await authService.isUserAuthenticated(userIdSend, token);
  if (!isAuth) {
    return res.status(401).json({ msg: 'not auth' });
  }

  // 2) Enfileira no Redis
  // Montamos o canal. Para evitar ambiguidade, podemos usar hífen: por exemplo, "1-4".
  const channelKey = `${userIdSend}${userIdReceive}`; 
  await queueService.enqueue(channelKey, message);

  return res.json({ message: 'message sended with success' });
});

module.exports = router;
