const Redis = require("ioredis");

class RedisQueueService {
  constructor() {
    if (!RedisQueueService.instance) {
      RedisQueueService.instance = new Redis({
        host: process.env.Redis_Host || "redis",
        port: process.env.Redis_port || 6379,
      });
    }
    this.client = RedisQueueService.instance;
  }

  async enqueue(channelKey, message) {
    await this.client.rpush(channelKey, message);
  }

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
