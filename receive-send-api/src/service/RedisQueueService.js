const Redis = require('ioredis');

class RedisQueueService {
  constructor() {
    if (!RedisQueueService.instance) {
      RedisQueueService.instance = new Redis({
        host: 'redis',
        port: 6379
      });
    }
    this.client = RedisQueueService.instance;
  }

  /**
   * Adiciona uma mensagem a uma fila (lista) de canal.
   * A chave da fila será exatamente o nome do canal (ex: "14" ou "2-1").
   */
  async enqueue(channelKey, message) {
    // push no final da lista
    await this.client.rpush(channelKey, message);
  }

  /**
   * Recupera TUDO de uma fila, esvaziando-a.
   * Em Redis, faremos LPOP até não ter mais.
   * Retorna array de mensagens.
   */
  async drainQueue(channelKey) {
    const msgs = [];
    while (true) {
      const msg = await this.client.lpop(channelKey);
      if (msg === null) break;
      msgs.push(msg);
    }
    return msgs;
  }
}

module.exports = RedisQueueService;
