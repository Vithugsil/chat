const express = require('express');
const router = express.Router();
const AuthService = require('../service/AuthService');
const MessageService = require('../service/MessageService');

const authService = new AuthService();
const messageService = new MessageService();

/**
 * GET /message?user=<userId>
 * Header: { Authorization: token }
 * Fluxo:
 *  1) Valida token + userId via Auth-API
 *  2) Se não autenticado, retorna { msg: 'not auth' }
 *  3) Senão, busca todos os usuários via AuthService.getAllUsers()
 *  4) Para cada user (diferente de userId), monta o canal e chama Record-API
 *  5) Retorna array de objetos no formato:
 *      [
 *        { userId: <outraPessoa>, msg: <texto> },
 *        ...
 *      ]
 */
router.get('/message', async (req, res) => {
  const token = req.header('Authorization') || '';
  const userId = parseInt(req.query.user || '0', 10);

  if (!token || !userId) {
    return res.status(400).json({ msg: 'dados insuficientes' });
  }

  // 1) Valida token
  const isAuth = await authService.isUserAuthenticated(userId, token);
  if (!isAuth) {
    return res.status(401).json({ msg: 'not auth' });
  }

  // 2) Obtém todos os usuários
  const allUsers = await authService.getAllUsers();
  // Filtra o próprio userId
  const otherUsers = allUsers.filter(u => u.id !== userId);

  // 3) Para cada usuário, busca mensagens do canal <outro><userId>
  const allMsgs = [];
  for (let u of otherUsers) {
    const channelKey = `${u.id}${userId}`;
    const msgs = await messageService.getMessagesByChannel(channelKey);
    // msgs é um array de { user_id_send, user_id_receive, message, created_at }
    // Mapeamos para o formato simplificado: { userId: <u.id>, msg: <texto> }
    for (let m of msgs) {
      allMsgs.push({
        userId: u.id,
        msg: m.message,
        timestamp: m.created_at
      });
    }
  }

  return res.json(allMsgs);
});

module.exports = router;
